/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import WCPCModal from '../../editor-components/wcpc-modal';
import useNewProductFieldset from '../../block-editor/course-pricing/use-new-product-fieldset';

/**
 * New product modal component.
 *
 * @param {Object}   props          Component propertes.
 * @param {Function} props.onClose  Modal close callback.
 * @param {Function} props.onCancel Modal cancel callback.
 * @param {Function} props.onSubmit Modal submit callback.
 */
const NewProductModal = ( { onClose, onCancel, onSubmit } ) => {
	const { fieldset, isSubmitting, saveProduct } = useNewProductFieldset( {
		fieldsetClassName: 'sensei-wcpc-new-product-modal__fieldset',
		fieldClassName: 'sensei-wcpc-new-product-modal__field',
		modalScope: true,
	} );

	const modalActions = [
		{
			id: 'cancel',
			label: __( 'Cancel', 'sensei-pro' ),
			buttonProps: {
				isSecondary: true,
				type: 'button',
				onClick: onCancel,
				disabled: isSubmitting,
			},
		},
		{
			id: 'create-product',
			label: (
				<>
					{ __( 'Create product', 'sensei-pro' ) }
					{ isSubmitting && <Spinner /> }
				</>
			),
			buttonProps: {
				className: 'sensei-wcpc-new-product__create-product-button',
				isPrimary: true,
				type: 'submit',
				disabled: isSubmitting,
			},
		},
	];

	return (
		<WCPCModal
			className="sensei-wcpc-new-product-modal"
			contentLabel={ __( 'Product creation', 'sensei-pro' ) }
			title={ __( 'Create a new product', 'sensei-pro' ) }
			actions={ modalActions }
			formProps={ {
				onSubmit: async ( e ) => {
					e.preventDefault();
					if ( await saveProduct() ) {
						onSubmit();
					}
				},
			} }
			onClose={ onClose }
		>
			{ fieldset }
		</WCPCModal>
	);
};

export default NewProductModal;
