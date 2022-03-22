/**
 * External dependencies
 */
import { find, uniq, difference, xorBy, remove, keyBy } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	registerStore,
	controls,
	createRegistrySelector,
	select as globalSelect,
} from '@wordpress/data';
import { controls as dataControls, apiFetch } from '@wordpress/data-controls';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import deprecated from '@wordpress/deprecated';

/**
 * Internal dependencies
 */
import editorLifecycle from 'sensei/assets/shared/helpers/editor-lifecycle';

/**
 * Products store actions.
 */
const actions = {
	/**
	 * Sets the non-membership products of the course after a change.
	 *
	 * @deprecated 2.4.0
	 *
	 * @param {Array} products The new products.
	 */
	setNonMembershipProducts: ( products ) => ( {
		type: 'DEPRECATED_SET_NORMAL_PRODUCTS',
		products,
	} ),

	/**
	 * Sets assignable products.
	 *
	 * @param {Object[]} products Assignable products.
	 * @param {boolean}  onTop    Whether to add the products to the top of the list.
	 */
	setAssignableProducts: ( products, onTop = false ) => ( {
		type: 'SET_ASSIGNABLE_PRODUCTS',
		ids: products.map( ( product ) => product.id ),
		entities: keyBy( products, 'id' ),
		onTop,
	} ),

	/**
	 * Sets linked membership products.
	 *
	 * @param {Object[]} products Membership products.
	 */
	setLinkedMembershipProducts: ( products ) => ( {
		type: 'SET_LINKED_MEMBERSHIP_PRODUCTS',
		ids: products.map( ( product ) => product.id ),
		entities: keyBy( products, 'id' ),
	} ),

	/**
	 * Toggle product.
	 *
	 * @param {number}  productId Product ID.
	 * @param {boolean} add       Whether product is being added or removed.
	 */
	*toggleProduct( productId, add ) {
		const selectedProductIds = ( yield controls.select(
			'core/editor',
			'getEditedPostAttribute',
			'meta'
		) )?._course_woocommerce_product;

		let newSelection;
		if ( add ) {
			newSelection = [ ...selectedProductIds, productId ];
		} else {
			newSelection = selectedProductIds.filter(
				( id ) => id !== productId
			);
		}

		yield controls.dispatch( 'core/editor', 'editPost', {
			meta: {
				_course_woocommerce_product: newSelection,
			},
		} );
	},

	/**
	 * Update all selected products.
	 *
	 * @param {number[]} newSelectedProductIds New selected product IDs.
	 */
	*updateSelectedProducts( newSelectedProductIds ) {
		yield controls.dispatch( 'core/editor', 'editPost', {
			meta: {
				_course_woocommerce_product: newSelectedProductIds,
			},
		} );
	},

	/**
	 * Toggle products in the modal scope.
	 *
	 * @param {number}  productId Product ID.
	 * @param {boolean} add       Whether product is being added or removed.
	 */
	toggleModalProduct: ( productId, add ) => ( {
		type: 'TOGGLE_MODAL_PRODUCT',
		productId,
		add,
	} ),

	/**
	 * Update all selected products in the modal scope.
	 *
	 * @param {number[]} productIds New selected product IDs.
	 */
	updateModalSelectedProducts: ( productIds ) => ( {
		type: 'UPDATE_MODAL_SELECTED_PRODUCTS',
		productIds,
	} ),

	/**
	 * Create a new product and select it.
	 * For the modal scope, it's selected only for that scope, otherwise
	 * it will be selected directly in the meta.
	 *
	 * @param {Object}  product             Product object.
	 * @param {string}  product.name        Product name.
	 * @param {string}  product.price       Product price.
	 * @param {string}  product.description Product description (used for description and short description).
	 * @param {boolean} modalScope          Whether it's the modal scope.
	 */
	*createProduct( { name, price, description }, modalScope = false ) {
		const response = yield apiFetch( {
			path: '/wc/v3/products',
			method: 'POST',
			data: {
				name,
				type: 'simple',
				regular_price: price,
				virtual: true,
				description,
				short_description: description,
			},
		} );

		yield actions.setAssignableProducts(
			[
				{
					id: response.id,
					name: response.name,
					description: response.short_description,
					price_html: response.price_html,
					linked_courses: [],
					total_sales: 0,
				},
			],
			true
		);

		if ( modalScope ) {
			yield actions.toggleModalProduct( response.id, true );
		} else {
			yield actions.toggleProduct( response.id, true );
		}

		// Log event.
		const course = yield controls.select( 'core/editor', 'getCurrentPost' );

		window.sensei_log_event( 'course_product_add', {
			course_id: course.id,
			is_modal: modalScope ? 1 : 0,
		} );
	},
};

/**
 * Product store selectors.
 */
const selectors = {
	/**
	 * Get all the course linked products.
	 *
	 * @deprecated 2.4.0
	 *
	 * @param {Object} state                    The state.
	 * @param {Object} state.normalProducts     The course non membership products.
	 * @param {Object} state.membershipProducts The course membership products.
	 */
	getProducts: ( { normalProducts, membershipProducts } ) => {
		deprecated( 'getProducts selector', {
			since: '2.4.0',
			alternative: 'getLinkedProducts and getLinkedMembershipProducts',
		} );

		return [ ...normalProducts, ...membershipProducts ];
	},

	/**
	 * Get course linked products.
	 *
	 * @param {Object}   state                               The state.
	 * @param {Object}   state.products                      Products.
	 * @param {number[]} state.products.assignableProductIds Assignable products IDs.
	 * @param {Object}   state.products.entities             Products entities.
	 * @param {string}   search                              Expression to search.
	 *
	 * @return {Object[]} Assignable products.
	 */
	getAssignableProducts: (
		{ products: { assignableProductIds, entities } },
		search
	) => {
		const products = assignableProductIds.map( ( id ) => entities[ id ] );

		if ( ! search ) {
			return products;
		}

		return products.filter( ( product ) =>
			product.name.match( new RegExp( search, 'i' ) )
		);
	},

	/**
	 * Get whether the site has assignable products.
	 *
	 * @param {Object}   state                               The state.
	 * @param {Object}   state.products                      Products.
	 * @param {number[]} state.products.assignableProductIds Assignable products IDs.
	 *
	 * @return {boolean} Assignable products.
	 */
	getHasAssignableProducts: ( { products: { assignableProductIds } } ) =>
		assignableProductIds.length > 0,

	/**
	 * Get linked products.
	 *
	 * @param {Object} state                   The state.
	 * @param {Object} state.products          Products.
	 * @param {Object} state.products.entities Products entities.
	 *
	 * @return {Object[]} Linked products.
	 */
	getLinkedProducts: createRegistrySelector(
		( select ) => ( { products: { entities } } ) => {
			const ids =
				select( 'core/editor' ).getEditedPostAttribute( 'meta' )
					?._course_woocommerce_product || [];

			return ids.map( ( id ) => entities?.[ id ] ).filter( ( e ) => e );
		}
	),

	/**
	 * Get toggled products with sales.
	 *
	 * @param {Object} state                   The state.
	 * @param {Object} state.products          Products.
	 * @param {Object} state.products.entities Products entities.
	 *
	 * @return {Object} Toggled products with sales.
	 */
	getToggledProductsWithSales: createRegistrySelector(
		( select ) => ( { products: { entities } } ) => {
			const currentIds =
				select( 'core/editor' ).getCurrentPostAttribute( 'meta' )
					?._course_woocommerce_product || [];

			const editedIds =
				select( 'core/editor' ).getEditedPostAttribute( 'meta' )
					?._course_woocommerce_product || [];

			const getDifferenceWithSales = ( inspectArray, excludeArray ) =>
				difference( inspectArray, excludeArray )
					.map( ( id ) => entities?.[ id ] )
					.filter( ( product ) => product.total_sales > 0 );

			return {
				added: getDifferenceWithSales( editedIds, currentIds ),
				removed: getDifferenceWithSales( currentIds, editedIds ),
			};
		}
	),

	/**
	 * Get selected products IDs in the modal scope.
	 *
	 * @param {Object} state                                  The state.
	 * @param {Object} state.products                         Products.
	 * @param {Object} state.products.modalSelectedProductIds Modal selected product IDs.
	 *
	 * @return {Object} Modal selected products.
	 */
	getModalSelectedProductIds: ( { products: { modalSelectedProductIds } } ) =>
		modalSelectedProductIds,

	/**
	 * Get linked membership products.
	 *
	 * @param {Object} state                                     The state.
	 * @param {Object} state.products                            Products.
	 * @param {Object} state.products.linkedMembershipProductIds Linked membership products.
	 * @param {Object} state.products.entities                   Products entities.
	 *
	 * @return {Object} Linked membership products.
	 */
	getLinkedMembershipProducts: ( {
		products: { linkedMembershipProductIds, entities },
	} ) => {
		return linkedMembershipProductIds
			.map( ( id ) => entities?.[ id ] )
			.filter( ( e ) => e );
	},

	/**
	 * Get product by ID.
	 *
	 * @param {Object} state                   The state.
	 * @param {Object} state.products          Products.
	 * @param {Object} state.products.entities Products entities.
	 * @param {number} productId               Product ID
	 *
	 * @return {Object} Product object.
	 */
	getProductById: ( { products: { entities } }, productId ) => {
		return entities[ productId ];
	},
};

/**
 * Product store resolvers.
 */
const resolvers = {
	/**
	 * Loads the products during initialization.
	 *
	 * @deprecated 2.4.0
	 */
	*getProducts() {
		const course = yield controls.select( 'core/editor', 'getCurrentPost' );

		const membershipProductIds = course.course_membership_products
			? course.course_membership_products
			: [];
		// eslint-disable-next-line camelcase
		const nonMembershipProductIds = course.meta?._course_woocommerce_product
			? course.meta._course_woocommerce_product
			: [];

		const linkedProducts = [
			...nonMembershipProductIds,
			...membershipProductIds,
		];

		if ( 0 === linkedProducts.length ) {
			return;
		}

		let courseProducts = {};

		try {
			courseProducts = yield apiFetch( {
				path: `/sensei-wcpc-internal/v1/course-products?include=${ linkedProducts.join(
					','
				) }`,
			} );
		} catch ( error ) {
			const errorMessage = sprintf(
				/* translators: Error message. */
				__(
					'An error was encountered while fetching products: %s',
					'sensei-lms'
				),
				error.message
			);
			yield controls.dispatch(
				'core/notices',
				'createErrorNotice',
				errorMessage,
				{ id: 'sensei-products-fetch-error' }
			);

			return;
		}

		if ( courseProducts?.products.length ) {
			// Enforce the order of linkedProducts meta to the products.
			const orderedProducts = uniq( linkedProducts )
				.map( ( linkedProduct ) =>
					find( courseProducts.products, { id: linkedProduct } )
				)
				.filter( ( product ) => product );

			const membershipProducts = remove( orderedProducts, ( product ) =>
				membershipProductIds.includes( product.id )
			);

			yield {
				type: 'DEPRECATED_SET_MEMBERSHIP_PRODUCTS',
				products: membershipProducts,
			};

			return actions.setNonMembershipProducts( orderedProducts );
		}
	},

	/**
	 * Loads the assignable products.
	 *
	 * @param {string} search Text to search.
	 */
	*getAssignableProducts( search ) {
		try {
			const course = yield controls.select(
				'core/editor',
				'getCurrentPost'
			);

			const path = addQueryArgs(
				'/sensei-wcpc-internal/v1/course-products',
				{
					course_id: course.id,
					per_page: -1,
					catalog_visibility: 'visible',
					search: search || undefined,
					linked_first: search ? undefined : true,
				}
			);

			const response = yield apiFetch( {
				path,
			} );

			const products = response?.products || [];

			yield actions.setAssignableProducts( products );
		} catch ( error ) {
			const errorMessage = sprintf(
				/* translators: Error message. */
				__(
					'An error was encountered while fetching assignable products: %s',
					'sensei-lms'
				),
				error.message
			);
			yield controls.dispatch(
				'core/notices',
				'createErrorNotice',
				errorMessage,
				{ id: 'sensei-assignable-products-fetch-error' }
			);
		}
	},

	/**
	 * Load linked products.
	 * To load the entities, it uses the `getAssignableProducts` resolver.
	 */
	*getLinkedProducts() {
		yield controls.select(
			COURSE_PRODUCTS_STORE,
			'getAssignableProducts',
			''
		);
	},

	/**
	 * Load linked membership products.
	 */
	*getLinkedMembershipProducts() {
		try {
			const course = yield controls.select(
				'core/editor',
				'getCurrentPost'
			);

			const membershipProductIds = course.course_membership_products
				? course.course_membership_products
				: [];

			if ( membershipProductIds.length > 0 ) {
				const response = yield apiFetch( {
					path: `/sensei-wcpc-internal/v1/course-products?include=${ membershipProductIds.join(
						','
					) }`,
				} );

				const products = response?.products || [];

				return actions.setLinkedMembershipProducts( products );
			}
		} catch ( error ) {
			const errorMessage = sprintf(
				/* translators: Error message. */
				__(
					'An error was encountered while fetching membership products: %s',
					'sensei-lms'
				),
				error.message
			);
			yield controls.dispatch(
				'core/notices',
				'createErrorNotice',
				errorMessage,
				{ id: 'sensei-membership-products-fetch-error' }
			);
		}
	},
};

/**
 * Product store reducer.
 *
 * @param {Object} state  The store state.
 * @param {Object} action The action to handle.
 */
const reducer = (
	state = {
		normalProducts: [], // deprecated 2.4.0
		membershipProducts: [], // deprecated 2.4.0
		products: {
			assignableProductIds: [],
			linkedMembershipProductIds: [],
			modalSelectedProductIds: [],
			entities: {},
		},
	},
	action
) => {
	switch ( action.type ) {
		case 'DEPRECATED_SET_NORMAL_PRODUCTS': // deprecated 2.4.0
			const normalProducts = action.products.filter(
				( { id } ) =>
					undefined === find( state.membershipProducts, { id } )
			);

			const diff = xorBy( state.normalProducts, normalProducts, 'id' );

			if ( diff.length === 0 ) {
				return state;
			}

			return {
				...state,
				normalProducts,
			};
		case 'DEPRECATED_SET_MEMBERSHIP_PRODUCTS': // deprecated 2.4.0
			const membershipsRemoved = state.normalProducts.filter(
				( { id } ) => undefined === find( action.products, { id } )
			);

			return {
				...state,
				membershipProducts: [ ...action.products ],
				normalProducts: membershipsRemoved,
			};
		case 'SET_ASSIGNABLE_PRODUCTS':
			const newProductIds = uniq(
				action.onTop
					? [ ...action.ids, ...state.products.assignableProductIds ]
					: [ ...state.products.assignableProductIds, ...action.ids ]
			);

			return {
				...state,
				products: {
					...state.products,
					assignableProductIds: newProductIds,
					entities: {
						...state.products.entities,
						...action.entities,
					},
				},
			};
		case 'SET_LINKED_MEMBERSHIP_PRODUCTS':
			return {
				...state,
				products: {
					...state.products,
					linkedMembershipProductIds: uniq( [
						...state.products.linkedMembershipProductIds,
						...action.ids,
					] ),
					entities: {
						...state.products.entities,
						...action.entities,
					},
				},
			};
		case 'TOGGLE_MODAL_PRODUCT':
			const modalSelectedProductIds = action.add
				? [
						...state.products.modalSelectedProductIds,
						action.productId,
				  ]
				: state.products.modalSelectedProductIds.filter(
						( id ) => id !== action.productId
				  );

			return {
				...state,
				products: {
					...state.products,
					modalSelectedProductIds,
				},
			};

		case 'UPDATE_MODAL_SELECTED_PRODUCTS':
			return {
				...state,
				products: {
					...state.products,
					modalSelectedProductIds: action.productIds,
				},
			};
		default:
			return state;
	}
};

export const COURSE_PRODUCTS_STORE = 'sensei-wc-paid-courses/products';

registerStore( COURSE_PRODUCTS_STORE, {
	reducer,
	actions,
	selectors,
	resolvers,
	controls: dataControls,
} );

// Log course product update event.
( () => {
	const coreEditorSelector = globalSelect( 'core/editor' );
	let productsBeforeSaving;

	editorLifecycle( {
		onSaveStart: () => {
			productsBeforeSaving =
				coreEditorSelector.getCurrentPostAttribute( 'meta' )
					?._course_woocommerce_product || [];
		},
		onSave: () => {
			const savedProducts =
				coreEditorSelector.getCurrentPostAttribute( 'meta' )
					?._course_woocommerce_product || [];

			const course = coreEditorSelector.getCurrentPost();

			if ( productsBeforeSaving !== savedProducts ) {
				window.sensei_log_event( 'course_product_update', {
					course_id: course.id,
					course_status: course.status,
					product_count: savedProducts.length,
				} );
			}
		},
	} );
} )();
