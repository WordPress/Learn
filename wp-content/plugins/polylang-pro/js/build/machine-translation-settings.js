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
			pllMachineTranslation.glossaries.init();
		} else {
			document.addEventListener( 'DOMContentLoaded', pllMachineTranslation.ajaxButton.attachEvent );
			document.addEventListener( 'DOMContentLoaded', pllMachineTranslation.dataUsage.fetchData );
			document.addEventListener( 'DOMContentLoaded', pllMachineTranslation.glossaries.init );
		}
		addAction( 'pll_settings_saved', 'polylang-pro', pllMachineTranslation.saveSettings.highlightRow );
		addAction( 'pll_settings_saved', 'polylang-pro', pllMachineTranslation.dataUsage.fetchData );
		addAction( 'pll_settings_saved', 'polylang-pro', pllMachineTranslation.glossaries.fetchDataOnSave );
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
			document
				.querySelectorAll( '.pll-progress-bar-wrapper' )
				.forEach( ( el ) => {
					const action = el.getAttribute( 'data-action' );
					const nonce = el.getAttribute( 'data-nonce' );
					const progress = el.querySelectorAll( 'div' ).item( 0 );
					const dataUsageRow = el.closest( 'tr' );

					if (
						! action ||
						! nonce ||
						! progress ||
						! dataUsageRow ||
						! el.parentElement
					) {
						return;
					}
					const description = el.parentElement
						.querySelectorAll( '.description' )
						.item( 0 );
					if ( ! description ) {
						return;
					}

					// Reset the progress bar.
					progress.style.width = '0%';
					progress.textContent = '';

					let spinner = el.querySelectorAll( '.spinner' ).item( 0 );

					// If the spinner no longer exists (replaced by text), recreate it.
					if ( ! spinner ) {
						spinner = document.createElement( 'span' );
						spinner.className = 'spinner pll-spinner-inline';
						// Delete the existing text (percentage) and add the spinner.
						el.innerHTML = '';
						el.appendChild( spinner );
						el.appendChild( progress );
					} else {
						// Display the spinner while loading.
						spinner.style.visibility = 'visible';
					}

					const apiKey = document
						.getElementById( 'pll-deepl-api-key' )
						.value.trim();
					if ( '' === apiKey ) {
						// Do not display the row and do not make the AJAX call if the API key is empty.
						dataUsageRow.style.display = 'none';
						return;
					}

					// Show the data usage row and the progress bar (in case it was hidden before).
					el.style.display = '';
					dataUsageRow.style.display = '';

					const urlParams = {
						action,
						_pll_nonce: nonce,
						pll_ajax_settings: 1,
					};
					const url = wp.url.addQueryArgs( ajaxurl, urlParams );

					fetch( url )
						.then( ( response ) => {
							return response.json();
						} )
						.then( ( json ) => {
							if ( ! json.success || ! json.data.percent ) {
								// No data - show the message but keep the row visible.
								el.style.display = 'none';
								description.textContent = json.data.message;
								return;
							}

							// Display a graphic.
							el.replaceChild(
								document.createTextNode(
									json.data.percent_formatted
								),
								spinner
							);
							progress.textContent = json.data.percent_formatted;
							progress.style.width = json.data.percent;
							description.textContent = json.data.message;
						} )
						.catch( () => {
							dataUsageRow.remove();
						} );
				} );
		},
	},

	glossaries: {
		/**
		 * Init.
		 */
		init: () => {
			const elems = pllMachineTranslation.glossaries.getElements();

			if ( ! elems.action ) {
				// Should not happen.
				return;
			}

			// On page load, refresh the `<select>` tag.
			pllMachineTranslation.glossaries.fetchData( elems );

			// On API key input blur, refresh the `<select>` tag.
			elems.apiKeyInput.addEventListener( 'blur', () => {
				const newApiKey = elems.apiKeyInput.value.trim();
				if ( newApiKey === elems.apiKey ) {
					// The API key hasn't changed.
					return;
				}
				elems.apiKey = newApiKey;
				elems.hasNewApiKey = true;
				pllMachineTranslation.glossaries.fetchData( elems );
			} );
		},

		/**
		 * Refreshes the `<select>` tag on option save.
		 * Hooked to `'pll_settings_saved'`.
		 */
		fetchDataOnSave: () => {
			const elems = pllMachineTranslation.glossaries.getElements();

			if ( ! elems.action ) {
				// Should not happen.
				return;
			}

			pllMachineTranslation.glossaries.fetchData( elems );
		},

		/**
		 * Returns data from the form.
		 *
		 * @return {Object} {
		 *     An object containing data from the form. The object can be empty on failure.
		 *
		 *     @type {HTMLElement} glossaryInput The glossary selector.
		 *     @type {string}      action        The AJAX action.
		 *     @type {string}      nonce         The nonce for the AJAX request.
		 *     @type {HTMLElement} apiKeyInput   The API key input.
		 *     @type {string}      apiKey        The API key.
		 * }
		 */
		getElements: () => {
			const glossaryInput =
				document.getElementById( 'pll-deepl-glossary' );

			if ( ! glossaryInput ) {
				return {};
			}

			const action = glossaryInput.getAttribute( 'data-action' );
			const nonce = glossaryInput.getAttribute( 'data-nonce' );

			if ( ! action || ! nonce ) {
				return {};
			}

			const apiKeyInput = document.getElementById( 'pll-deepl-api-key' );

			if ( ! apiKeyInput ) {
				pllMachineTranslation.glossaries.removeOptions( glossaryInput );
				return {};
			}

			const apiKey = apiKeyInput.value.trim();

			return { glossaryInput, action, nonce, apiKeyInput, apiKey };
		},

		/**
		 * Refreshes the `<select>` tag.
		 *
		 * @param {Object}      elems                      An object containing data from the form.
		 * @param {HTMLElement} elems.glossaryInput        The glossary selector.
		 * @param {string}      elems.action               The AJAX action.
		 * @param {string}      elems.nonce                The nonce for the AJAX request.
		 * @param {HTMLElement} elems.apiKeyInput          The API key input.
		 * @param {string}      elems.apiKey               The API key.
		 * @param {boolean}     [elems.hasNewApiKey=false] Optional. Tells if the given API key must be sent in the AJAX request. Default is `false`.
		 */
		fetchData: ( elems ) => {
			if ( elems.hasNewApiKey && '' === elems.apiKey ) {
				// No need to request the API.
				pllMachineTranslation.glossaries.removeOptions(
					elems.glossaryInput
				);
				return;
			}

			elems.glossaryInput.setAttribute( 'disabled', 'disabled' );

			const urlParams = {
				action: elems.action,
				_pll_nonce: elems.nonce,
				pll_ajax_settings: 1,
			};
			if ( elems.hasNewApiKey ) {
				urlParams.api_key = elems.apiKey;
			}
			const url = wp.url.addQueryArgs( ajaxurl, urlParams );

			fetch( url )
				.then( ( response ) => {
					return response.json();
				} )
				.then( ( json ) => {
					// First, empty the selector.
					pllMachineTranslation.glossaries.removeOptions(
						elems.glossaryInput
					);

					if ( ! json.success || ! json.data.glossaries ) {
						// `json.success` being `false` may mean the API key is invalid.
						elems.glossaryInput.removeAttribute( 'disabled' );
						return;
					}

					// Insert new choices.
					Object.entries( json.data.glossaries ).forEach(
						( [ key, value ] ) => {
							const opt = document.createElement( 'option' );
							opt.value = key;
							opt.textContent = value;
							if ( key === json.data.selected ) {
								opt.selected = true;
							}
							elems.glossaryInput.appendChild( opt );
						}
					);

					elems.glossaryInput.removeAttribute( 'disabled' );
				} )
				.catch( () => {
					elems.glossaryInput.removeAttribute( 'disabled' );
				} );
		},

		/**
		 * Removes the `<option>` tags in the given `<select>` element, except the one with an empty value.
		 *
		 * @param {HTMLElement} select The parent element.
		 */
		removeOptions: ( select ) => {
			Object.entries( select.children ).forEach( ( [ , option ] ) => {
				if ( option.value.trim() !== '' ) {
					option.remove();
				}
			} );
		},
	},
};

pllMachineTranslation.init();

