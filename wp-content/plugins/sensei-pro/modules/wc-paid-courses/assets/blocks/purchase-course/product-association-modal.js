/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useState, useEffect, RawHTML } from '@wordpress/element';
import {
	CheckboxControl,
	Button,
	Icon,
	Spinner,
	Tooltip,
} from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { search as searchIcon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import useSearch from '../../block-editor/course-pricing/use-search';
import WCPCModal from '../../editor-components/wcpc-modal';
import NewProductModal from './new-product-modal';
import { COURSE_PRODUCTS_STORE } from '../../block-editor/course-pricing/store';

/**
 * @typedef ProductsSelectionHookReturn
 *
 * @property {number[]} selectedProductIds Selected product IDs.
 * @property {Function} toggleProduct      Toggle product selection handler.
 * @property {Function} submitSelection    Submit the selection.
 */
/**
 * Product selection hook.
 *
 * @param {number[]} initialSelectedProductIds Initial selected products IDs.
 *
 * @return {ProductsSelectionHookReturn} Hook object.
 */
const useProductsSelection = ( initialSelectedProductIds ) => {
	const { selectedProductIds } = useSelect( ( select ) => ( {
		selectedProductIds: select(
			COURSE_PRODUCTS_STORE
		).getModalSelectedProductIds(),
	} ) );

	const {
		updateSelectedProducts,
		updateModalSelectedProducts,
		toggleModalProduct,
	} = useDispatch( COURSE_PRODUCTS_STORE );

	useEffect( () => {
		updateModalSelectedProducts( initialSelectedProductIds );
	}, [ initialSelectedProductIds ] );

	const submitSelection = async () => {
		await updateSelectedProducts( selectedProductIds );
	};

	return {
		selectedProductIds,
		toggleProduct: toggleModalProduct,
		submitSelection,
	};
};

/**
 * Product association modal component.
 *
 * @param {Object}   props                           Component propertes.
 * @param {number[]} props.initialSelectedProductIds Initial selected products IDs.
 * @param {Function} props.onSubmit                  Modal submit callback.
 * @param {Function} props.onClose                   Modal close callback.
 */
const ProductAssociationModal = ( {
	initialSelectedProductIds,
	onSubmit,
	onClose,
} ) => {
	const {
		selectedProductIds,
		toggleProduct,
		submitSelection,
	} = useProductsSelection( initialSelectedProductIds );

	const {
		products,
		isLoadingProducts,
		hasAssignableProducts,
		onSearch,
	} = useSearch();

	// New product modal.
	const [ isNewProductModalActive, setNewProductModalActive ] = useState(
		false
	);

	if ( isNewProductModalActive ) {
		return (
			<NewProductModal
				onClose={ onClose }
				onCancel={ () => setNewProductModalActive( false ) }
				onSubmit={ () => setNewProductModalActive( false ) }
			/>
		);
	}

	let searchContent;

	if ( isLoadingProducts ) {
		searchContent = (
			<div className="sensei-wcpc-product-association-modal__loading">
				<Spinner />
			</div>
		);
	} else if ( ! hasAssignableProducts ) {
		searchContent = (
			<div>
				<p>
					{ __(
						'You don’t have any products yet. Get started by creating a new WooCommerce product.',
						'sensei-pro'
					) }
				</p>
				<Button
					isSecondary
					isSmall
					onClick={ () => setNewProductModalActive( true ) }
				>
					{ __( 'Create a product', 'sensei-pro' ) }
				</Button>
			</div>
		);
	} else if ( products.length === 0 ) {
		searchContent = <p>{ __( 'No products found.', 'sensei-pro' ) }</p>;
	} else {
		searchContent = (
			<table className="sensei-wcpc-product-association-modal__products-table">
				<thead>
					<tr>
						<th>{ __( 'Select the product', 'sensei-pro' ) }</th>
						<th>{ __( 'Product', 'sensei-pro' ) }</th>
						<th>{ __( 'Price', 'sensei-pro' ) }</th>
						<th>{ __( 'Linked courses', 'sensei-pro' ) }</th>
						<th>{ __( 'Purchases', 'sensei-pro' ) }</th>
					</tr>
				</thead>
				<tbody>
					{ products.map( ( product ) => (
						<tr key={ product.id }>
							<td className="sensei-wcpc-product-association-modal__products-table__checkbox-column">
								<CheckboxControl
									id={ `product-selection-${ product.id }` }
									checked={ selectedProductIds.includes(
										product.id
									) }
									onChange={ ( checked ) =>
										toggleProduct( product.id, checked )
									}
								/>
							</td>
							<td>
								<label
									className="sensei-wcpc-product-association-modal__products-table__product-name"
									htmlFor={ `product-selection-${ product.id }` }
								>
									{ product.name }
								</label>
							</td>
							<td>
								<RawHTML>{ product.price_html }</RawHTML>
							</td>
							<td>
								{ product.linked_courses.length === 0
									? __( 'No linked courses', 'sensei-pro' )
									: sprintf(
											// translators: placeholder is number of linked courses.
											_n(
												'%d linked course',
												'%d linked courses',
												product.linked_courses.length,
												'sensei-pro'
											),
											product.linked_courses.length
									  ) }
							</td>
							<td>
								{ product.total_sales > 0 ? (
									<Tooltip
										text={ __(
											'Toggling this product may affect existing enrollments.',
											'sensei-pro'
										) }
									>
										<span>
											{ __(
												'Has purchases',
												'sensei-pro'
											) }
										</span>
									</Tooltip>
								) : (
									__( 'No purchases', 'sensei-pro' )
								) }
							</td>
						</tr>
					) ) }
				</tbody>
			</table>
		);
	}

	const modalActions = [
		{
			id: 'selected',
			label: sprintf(
				// translators: placeholder is number of selected products.
				_n(
					'%d product selected',
					'%d products selected',
					selectedProductIds.length,
					'sensei-pro'
				),
				selectedProductIds.length
			),
		},
		{
			id: 'button',
			label:
				initialSelectedProductIds.length === 0
					? __( 'Link products', 'sensei-pro' )
					: __( 'Update', 'sensei-pro' ),
			buttonProps: {
				isPrimary: true,
				onClick: async () => {
					await submitSelection();
					onSubmit( selectedProductIds );
					onClose();
				},
			},
		},
	];

	return (
		<WCPCModal
			className="sensei-wcpc-product-association-modal"
			contentLabel={ __( 'Course products association', 'sensei-pro' ) }
			title={ __(
				'Add pricing by linking one or more products to this course',
				'sensei-pro'
			) }
			intro={ __(
				'To access this course, learners will need to purchase one of the assigned products.',
				'sensei-pro'
			) }
			actions={ modalActions }
			onClose={ onClose }
		>
			<div className="sensei-wcpc-product-association-modal__actions">
				<div className="sensei-wcpc-product-association-modal__search">
					<input
						className="sensei-wcpc-product-association-modal__search__input"
						type="search"
						title={ __( 'Search for a product', 'sensei-pro' ) }
						placeholder={ __(
							'Search for a product',
							'sensei-pro'
						) }
						onChange={ ( e ) => onSearch( e.target.value ) }
					/>
					<span className="sensei-wcpc-product-association-modal__search__icon">
						<Icon icon={ searchIcon } />
					</span>
				</div>

				<Button
					isTertiary
					onClick={ () => setNewProductModalActive( true ) }
				>
					{ __( 'Create a new product', 'sensei-pro' ) }
				</Button>
			</div>

			<div className="sensei-wcpc-product-association-modal__products">
				{ searchContent }
			</div>
		</WCPCModal>
	);
};

export default ProductAssociationModal;
