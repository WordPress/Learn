/**
 * @package Polylang-Pro
 */

const { addAction } = wp.hooks;

const pllMachineTranslation = {
	/**
	 * Init.
	 */
	init: () => {
		if ( document.readyState !== 'loading' ) {
			pllMachineTranslation.ajaxButton.attachEvent();
			pllMachineTranslation.dataUsage.fetchData();
		} else {
			document.addEventListener( 'DOMContentLoaded', pllMachineTranslation.ajaxButton.attachEvent );
			document.addEventListener( 'DOMContentLoaded', pllMachineTranslation.dataUsage.fetchData );
		}
		addAction( 'pll_settings_saved', 'polylang-pro', pllMachineTranslation.saveSettings.highlightRow );
	},

	/**
	 * Resets a field's row.
	 *
	 * @param {HTMLElement} fieldRow Field's row.
	 */
	resetFieldRow: ( fieldRow ) => {
		fieldRow.querySelectorAll( '.pll-message-shown' ).forEach( ( el ) => {
			el.classList.remove( 'pll-message-shown' );
		} );
		fieldRow.classList.remove( 'notice-success', 'notice-warning', 'notice-error', 'notice-alt' );
		fieldRow.querySelectorAll( '.pll-error-message-text' ).forEach( ( el ) => {
			el.textContent = '';
		} );
	},

	/**
	 * Displays an error message under the field by adding HTML classes.
	 *
	 * @param {HTMLElement} fieldRow     Field's row.
	 * @param {String}      messageClass HTML class of the error message to display.
	 * @param {String}      type         Type of the error: `'error'` or `'warning'`.
	 */
	displayErrorMessage: ( fieldRow, messageClass, type = 'error' ) => {
		if ( messageClass ) {
			fieldRow.querySelectorAll( '.' + messageClass ).forEach( ( el ) => {
				el.classList.add( 'pll-message-shown' );
			} );
		}
		fieldRow.classList.add( 'notice-' + type, 'notice-alt' );
	},

	ajaxButton: {
		/**
		 * Attaches an event to `.pll-ajax-button` buttons to trigger AJAX requests.
		 */
		attachEvent: () => {
			document.querySelectorAll( '.pll-ajax-button' ).forEach( ( el ) => {
				el.addEventListener( 'click', ( event ) => {
					const button    = event.target;
					const action    = button.getAttribute( 'data-action' );
					const nonce     = button.getAttribute( 'data-nonce' );
					const fieldRow  = button.closest( 'tr' );
					const errorElms = fieldRow.querySelectorAll( '.pll-error-message-text' );

					if ( ! action || ! nonce || ! fieldRow || ! errorElms.length || button.getAttribute( 'disabled' ) ) {
						return;
					}

					const urlParams = { 'action': action, '_pll_nonce': nonce, 'pll_ajax_settings': 1 };
					fieldRow.querySelectorAll( '[data-name]' ).forEach( ( el ) => {
						urlParams[ el.getAttribute( 'data-name' ) ] = el.value;
					} );
					const url = wp.url.addQueryArgs( ajaxurl, urlParams );

					button.setAttribute( 'disabled', 'disabled' );
					pllMachineTranslation.resetFieldRow( fieldRow );

					fetch( url ).then( ( response ) => {
						return response.json();
					} ).then( ( json ) => {
						button.removeAttribute( 'disabled' );

						if ( json.success ) {
							fieldRow.classList.add( 'notice-success', 'notice-alt' );
						} else {
							errorElms[0].textContent = json.data && json.data.message ? json.data.message : '';
							pllMachineTranslation.displayErrorMessage( fieldRow, json.data ? json.data.message_class : '' );
						}
					} ).catch( () => {
						button.removeAttribute( 'disabled' );
						fieldRow.classList.add( 'notice-error', 'notice-alt' );
					} );
				} );
			} );
		}
	},

	saveSettings: {
		/**
		 * Highlights a settings row in case of error when the settings are saved.
		 * Hooked to `'pll_settings_saved'`.
		 *
		 * @param {Object}      response The response from the AJAX call.
		 * @param {HTMLElement} tr       The HTML element containing the module's fields.
		 */
		highlightRow: ( response, tr ) => {
			switch ( response.what ) {
				case 'success':
					tr.querySelectorAll( '.notice-alt, .pll-message-shown' ).forEach( ( el ) => {
						el.classList.remove( 'notice-success', 'notice-warning', 'notice-error', 'notice-alt', 'pll-message-shown' );
					} );
					break;

				case 'error':
					const noticeData = pllMachineTranslation.saveSettings.getNoticeData( response.data );

					if ( ! noticeData.fieldId ) {
						break;
					}

					const field = document.getElementById( noticeData.fieldId );

					if ( ! field ) {
						break;
					}

					const fieldRow = field.closest( 'tr' );

					if ( ! fieldRow ) {
						break;
					}

					pllMachineTranslation.resetFieldRow( fieldRow );
					pllMachineTranslation.displayErrorMessage( fieldRow, noticeData.messageClass, noticeData.type );
					break;
			}
		},

		/**
		 * Returns the data contained in the HTML classes of the given element.
		 *
		 * @param {String} htmlString HTML string.
		 * @returns {Object}
		 */
		getNoticeData: ( htmlString ) => {
			const div = document.createElement( 'div' );
			div.innerHTML = htmlString.trim(); // phpcs:ignore WordPressVIPMinimum.JS.InnerHTML.Found
			return {
				type:         pllMachineTranslation.saveSettings.find( div.firstChild.className, 'notice-(success|warning|error)', 'error' ),
				fieldId:      pllMachineTranslation.saveSettings.find( div.firstChild.className, 'pll-field-id-([^\\s]+)', '' ), // See `Settings\Deepl::is_api_key_valid()` and `Module_Settings::update()`.
				messageClass: pllMachineTranslation.saveSettings.find( div.firstChild.className, 'pll-message-class-([^\\s]+)', '' ) // See `Settings\Deepl::is_api_key_valid()` and `Module_Settings::update()`.
			};
		},

		/**
		 * Returns the part of the given string matching the given pattern.
		 *
		 * @param {String} string  A string.
		 * @param {String} pattern A regex pattern.
		 * @param {String} def     String to return if nothing is found.
		 * @returns {String}
		 */
		find: ( string, pattern, def ) => {
			const matches = ( ' ' + string + ' ' ).match( new RegExp( '\\s' + pattern + '\\s' ) );
			return matches && matches[1] ? matches[1] : def;
		}
	},

	dataUsage: {
		fetchData: () => {
			document.querySelectorAll( '.pll-progress-bar-wrapper' ).forEach( ( el ) => {
				const action   = el.getAttribute( 'data-action' );
				const nonce    = el.getAttribute( 'data-nonce' );
				const spinner  = el.querySelectorAll( '.spinner' ).item( 0 );
				const progress = el.querySelectorAll( 'div' ).item( 0 );

				if ( ! action || ! nonce || ! spinner || ! progress || ! el.parentElement ) {
					return;
				}

				const description = el.parentElement.querySelectorAll( '.description' ).item( 0 );

				if ( ! description ) {
					return;
				}

				const urlParams = { 'action': action, '_pll_nonce': nonce, 'pll_ajax_settings': 1 };
				const url       = wp.url.addQueryArgs( ajaxurl, urlParams );

				fetch( url ).then( ( response ) => {
					return response.json();
				} ).then( ( json ) => {
					if ( ! json.success || ! json.data.percent ) {
						/*
						 * 2 cases:
						 * - Error while retrieving the data: display the error message.
						 * - The character limit is 0: display only the character count.
						 */
						el.remove();
						description.textContent = json.data.message;
						return;
					}

					// Display a graphic.
					el.replaceChild( document.createTextNode( json.data.percent_formatted ), spinner );
					progress.textContent    = json.data.percent_formatted;
					progress.style.width    = json.data.percent;
					description.textContent = json.data.message;
				} ).catch( () => {
					el.closest( 'tr' ).remove();
				} );
			} );
		}
	}
};

pllMachineTranslation.init();

