/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import ProductAssociationModal from './product-association-modal';
import EnrollmentUpdatesModal from '../../block-editor/course-pricing/enrollment-updates-modal';
import ToolbarDropdown from 'sensei/assets/blocks/editor-components/toolbar-dropdown';
import { COURSE_PRODUCTS_STORE } from '../../block-editor/course-pricing/store';

/**
 * Price selection toolbar component.
 *
 * @param {Object}  props
 * @param {boolean} props.hasMemberships Whether it has associated memberships.
 */
const PriceSelectionToolbar = ( { hasMemberships } ) => {
	const { updateSelectedProducts } = useDispatch( COURSE_PRODUCTS_STORE );

	const { metaSelectedProductIds } = useSelect( ( select ) => ( {
		metaSelectedProductIds: select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		)._course_woocommerce_product,
	} ) );

	const { courseId } = useSelect( ( select ) => ( {
		courseId: select( 'core/editor' ).getCurrentPost()?.id,
	} ) );

	const [
		isProductAssociationModalActive,
		setProductAssociationModalActive,
	] = useState( false );

	const [
		enrollmentUpdatesModalOpener,
		setEnrollmentUpdatesModalOpener,
	] = useState( false );

	const onPricingChange = async ( value ) => {
		if ( 'free' === value ) {
			await updateSelectedProducts( [] );
			setEnrollmentUpdatesModalOpener( 'free' );
		} else {
			setProductAssociationModalActive( true );
		}

		window.sensei_log_event( 'course_signup_block_pricing_select', {
			course_id: courseId,
			type: value,
		} );
	};

	const pricingOptions = [
		{
			label: hasMemberships
				? __( 'Membership only', 'sensei-pro' )
				: __( 'Free', 'sensei-pro' ),
			value: 'free',
		},
		{
			label: __( 'Paid', 'sensei-pro' ),
			value: 'paid',
		},
	];

	return (
		<>
			<ToolbarDropdown
				options={ pricingOptions }
				optionsLabel={ __( 'Course pricing', 'sensei-pro' ) }
				value={ metaSelectedProductIds.length > 0 ? 'paid' : 'free' }
				onChange={ onPricingChange }
			/>
			{ isProductAssociationModalActive && (
				<ProductAssociationModal
					initialSelectedProductIds={ metaSelectedProductIds }
					onSubmit={ ( selectedProductIds ) => {
						setEnrollmentUpdatesModalOpener( 'modal' );
						window.sensei_log_event(
							'course_signup_block_pricing_modal_submit',
							{
								course_id: courseId,
								product_count: selectedProductIds.length,
							}
						);
					} }
					onClose={ () => setProductAssociationModalActive( false ) }
				/>
			) }
			{ enrollmentUpdatesModalOpener && (
				<EnrollmentUpdatesModal
					onBack={
						'modal' === enrollmentUpdatesModalOpener
							? () => {
									setEnrollmentUpdatesModalOpener( false );
									setProductAssociationModalActive( true );
							  }
							: undefined
					}
					onClose={ () => setEnrollmentUpdatesModalOpener( false ) }
				/>
			) }
		</>
	);
};

export default PriceSelectionToolbar;
