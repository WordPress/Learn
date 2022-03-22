/**
 * External dependencies
 */
import { uniqBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { Spinner, ToolbarGroup } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { BlockControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { EditPurchaseButton } from './edit';
import PriceSelectionToolbar from './price-selection-toolbar';
import { COURSE_PRODUCTS_STORE } from '../../block-editor/course-pricing/store';

/**
 * This function returns a component which displays the Purchase Button block if there are any products linked to the
 * course.
 *
 * @param {Object} EditTakeCourse EditTakeCourse component.
 */
export default ( EditTakeCourse ) => ( props ) => {
	const { products, membershipProducts } = useSelect( ( select ) => ( {
		products: select( COURSE_PRODUCTS_STORE ).getLinkedProducts(),
		membershipProducts: select(
			COURSE_PRODUCTS_STORE
		).getLinkedMembershipProducts(),
	} ) );

	const areProductsLoaded = useSelect(
		( select ) =>
			// Invoked by `getLinkedProducts` selector.
			select(
				COURSE_PRODUCTS_STORE
			).hasFinishedResolution( 'getAssignableProducts', [ '' ] ) &&
			select( COURSE_PRODUCTS_STORE ).hasFinishedResolution(
				'getLinkedMembershipProducts'
			)
	);

	if ( props.attributes.isPreview ) {
		return <EditTakeCourse { ...props } />;
	}

	if ( ! areProductsLoaded ) {
		return (
			<div className="wp-block-sensei-lms-purchase-course__centered-spinner">
				<Spinner />
			</div>
		);
	}

	const allProducts = uniqBy( [ ...products, ...membershipProducts ], 'id' );

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<PriceSelectionToolbar
						hasMemberships={ membershipProducts.length > 0 }
					/>
				</ToolbarGroup>
			</BlockControls>
			{ allProducts.length ? (
				<EditPurchaseButton
					products={ allProducts }
					EditTakeCourse={ EditTakeCourse }
					{ ...props }
				/>
			) : (
				<EditTakeCourse { ...props } />
			) }
		</>
	);
};
