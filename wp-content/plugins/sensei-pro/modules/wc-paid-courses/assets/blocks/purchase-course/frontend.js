import { __ } from '@wordpress/i18n';

( () => {
	/**
	 * This class is responsible to update the form, based on the
	 * selected product.
	 */
	class ProductSelection {
		/**
		 * Product selection class constructor.
		 *
		 * @param {Object} form Form DOM object.
		 */
		constructor( form ) {
			if ( ! form ) {
				return;
			}

			this.form = form;
			this.button = form.querySelector( 'button' );
			this.variationProductDataHiddenClass = 'variation-data-hidden';

			// This setTimeout avoid a Chrome issue while getting the selected radio after the page load.
			setTimeout( () => {
				this.init();
			}, 1 );
		}

		/**
		 * Init product selection feature.
		 */
		init = () => {
			const selectedRadio = this.form.querySelector(
				'input[name="product_id"]:checked'
			);
			const radios = this.form.querySelectorAll(
				'input[name="product_id"]'
			);

			radios.forEach( ( radio ) => {
				radio.addEventListener( 'change', this.onChangeHandler );
			} );
			this.updateData( selectedRadio );
		};

		/**
		 * Clear current data.
		 */
		clearData = () => {
			this.form
				.querySelectorAll(
					`.${ this.variationProductDataHiddenClass }`
				)
				.forEach( ( input ) => {
					input.parentNode.removeChild( input );
				} );
		};

		/**
		 * Update the form action, price, and add hidden inputs to the form.
		 *
		 * @param {Object} radio Radio DOM object.
		 */
		updateData = ( radio ) => {
			const data = radio.dataset;
			const priceHTML = radio.parentNode.querySelector(
				'.wp-block-sensei-lms-purchase-course__products__price'
			).innerHTML;

			this.form.setAttribute( 'action', data.action );
			this.button.innerHTML =
				priceHTML + ' - ' + __( 'Purchase Course', 'sensei-pro' );

			this.clearData();

			Object.entries( data ).forEach( ( [ name, value ] ) => {
				const input = document.createElement( 'input' );
				input.setAttribute( 'type', 'hidden' );
				input.setAttribute( 'name', name );
				input.setAttribute( 'value', value );
				input.setAttribute(
					'class',
					this.variationProductDataHiddenClass
				);

				this.form.appendChild( input );
			} );
		};

		/**
		 * Radio button change handler.
		 *
		 * @param {Object} e Event object.
		 */
		onChangeHandler = ( e ) => {
			this.updateData( e.target );
		};
	}

	document
		.querySelectorAll( '.multiple-products-form' )
		.forEach( ( element ) => {
			new ProductSelection( element );
		} );
} )();
