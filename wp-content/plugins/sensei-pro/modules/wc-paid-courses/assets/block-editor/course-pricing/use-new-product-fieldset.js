/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useState, RawHTML } from '@wordpress/element';
import { TextControl, TextareaControl, Notice } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { COURSE_PRODUCTS_STORE } from './store';

const currencySymbol =
	window.senseiWcPaidCoursesBlockEditorData.wc_currency_symbol;

/**
 * New product fieldset hook.
 * It returns the product fields and the form state.
 *
 * @param {Object}  options                   Hook options.
 * @param {string}  options.fieldsetClassName Fieldset class name.
 * @param {string}  options.fieldClassName    Field class name.
 * @param {boolean} options.modalScope        Whether it's the modal scope.
 *
 * @return {{fieldset:Object, formState:Object, saveProduct:Function}} Hook object.
 */
const useNewProductFieldset = ( {
	fieldsetClassName,
	fieldClassName,
	modalScope = false,
} = {} ) => {
	const { createProduct } = useDispatch( COURSE_PRODUCTS_STORE );

	const [ formState, setFormState ] = useState( {} );

	const [ isSubmitting, setIsSubmitting ] = useState( false );

	const [ error, setError ] = useState( null );

	const createFieldProps = ( name ) => ( {
		value: formState[ name ] || '',
		onChange: ( value ) =>
			setFormState( ( prevState ) => ( {
				...prevState,
				[ name ]: value,
			} ) ),
	} );

	return {
		fieldset: (
			<fieldset className={ fieldsetClassName }>
				{ error && (
					<Notice
						className="sensei-wcpc-new-product__error"
						status="error"
						isDismissible={ false }
					>
						<RawHTML>{ error }</RawHTML>
					</Notice>
				) }

				<TextControl
					className={ fieldClassName }
					label={ __( 'Name', 'sensei-pro' ) }
					required
					{ ...createFieldProps( 'name' ) }
				/>
				<TextControl
					className={ fieldClassName }
					label={
						<>
							{ __( 'Price', 'sensei-pro' ) }
							{ currencySymbol && (
								<RawHTML style={ { display: 'inline' } }>
									{ ` (${ currencySymbol })` }
								</RawHTML>
							) }
						</>
					}
					type="number"
					required
					{ ...createFieldProps( 'price' ) }
				/>
				<TextareaControl
					className={ fieldClassName }
					label={ __( 'Description (Optional)', 'sensei-pro' ) }
					{ ...createFieldProps( 'description' ) }
				/>
			</fieldset>
		),
		formState,
		isSubmitting,
		saveProduct: async () => {
			let success = false;
			setIsSubmitting( true );

			try {
				await createProduct( formState, modalScope );
				setFormState( {} );
				setError( null );
				success = true;
			} catch ( err ) {
				setError(
					sprintf(
						/* translators: Error message. */
						__(
							'An error was encountered while creating the product: %s',
							'sensei-pro'
						),
						err.message
					)
				);
			}

			setIsSubmitting( false );

			return success;
		},
	};
};

export default useNewProductFieldset;
