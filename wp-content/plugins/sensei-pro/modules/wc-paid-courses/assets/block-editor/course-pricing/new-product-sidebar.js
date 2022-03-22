/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import useNewProductFieldset from './use-new-product-fieldset';

/**
 * New Products Sidebar component.
 *
 * @param {Object}   props                  Component props.
 * @param {boolean}  props.showCreateButton Whether show create button.
 * @param {Function} props.isFormActive     Whether form is active.
 * @param {Function} props.setFormActive    Set form active function.
 */
const NewProductSidebar = ( {
	showCreateButton,
	isFormActive,
	setFormActive,
} ) => {
	const { fieldset, isSubmitting, saveProduct } = useNewProductFieldset( {
		fieldsetClassName: 'sensei-wcpc-course-pricing__new-product-fieldset',
	} );

	const submitHandler = async ( e ) => {
		e.preventDefault();
		if ( await saveProduct() ) {
			setFormActive( false );
		}
	};

	if ( isFormActive ) {
		return (
			<form onSubmit={ submitHandler }>
				{ fieldset }

				<ul className="sensei-wcpc-new-product__action-buttons">
					<li>
						<Button
							className="sensei-wcpc-new-product__create-product-button"
							isSecondary
							type="submit"
							disabled={ isSubmitting }
						>
							{ __( 'Create product', 'sensei-pro' ) }
							{ isSubmitting && <Spinner /> }
						</Button>
					</li>
					<li>
						<Button
							isTertiary
							disabled={ isSubmitting }
							onClick={ () => setFormActive( false ) }
						>
							{ __( 'Cancel', 'sensei-pro' ) }
						</Button>
					</li>
				</ul>
			</form>
		);
	}

	if ( showCreateButton ) {
		return (
			<Button isTertiary onClick={ () => setFormActive( true ) }>
				{ __( 'Create a new product', 'sensei-pro' ) }
			</Button>
		);
	}

	return null;
};

export default NewProductSidebar;
