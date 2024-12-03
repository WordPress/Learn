/******/ "use strict";
/******/ // The require scope
/******/ var __webpack_require__ = {};
/******/ 
/************************************************************************/
/******/ /* webpack/runtime/define property getters */
/******/ (() => {
/******/ 	// define getter functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ })();
/******/ 
/******/ /* webpack/runtime/hasOwnProperty shorthand */
/******/ (() => {
/******/ 	__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ })();
/******/ 
/************************************************************************/
var __webpack_exports__ = {};
/* unused harmony exports initializeConfirmationModal, initializeLanguageOldValue */
/**
 * @package Polylang
 */

const languagesList = jQuery( '.post_lang_choice' );

// Dialog box for alerting the user about a risky changing.
const initializeConfirmationModal = () => {
	// We can't use underscore or lodash in this common code because it depends of the context classic or block editor.
	// Classic editor underscore is loaded, Block editor lodash is loaded.
	const { __ } = wp.i18n;

	// Create dialog container.
	const dialogContainer = jQuery(
		'<div/>',
		{
			id: 'pll-dialog',
			style: 'display:none;'
		}
	).text( __( 'Are you sure you want to change the language of the current content?', 'polylang' ) );

	// Put it after languages list dropdown.
	// PHPCS ignore dialogContainer is a new safe HTML code generated above.
	languagesList.after( dialogContainer ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.after

	const dialogResult = new Promise(
		( confirm, cancel ) => {
			const confirmDialog = ( what ) => { // phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent
				switch ( what ) { // phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent
					case 'yes':
						// Confirm the new language.
						languagesList.data( 'old-value', languagesList.children( ':selected' ).first().val() );
						confirm();
						break;
					case 'no':
						// Revert to the old language.
						languagesList.val( languagesList.data( 'old-value' ) );
						cancel( 'Cancel' );
						break;
				}
				dialogContainer.dialog( 'close' ); // phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent
			} // phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent

			// Initialize dialog box in the case a language is selected but not added in the list.
			const dialogOptions = {
				autoOpen: false,
				modal: true,
				draggable: false,
				resizable: false,
				title: __( 'Change language', 'polylang' ),
				minWidth: 600,
				maxWidth: '100%',
				open: function ( event, ui ) {
					// Change dialog box position for rtl language
					if ( jQuery( 'body' ).hasClass( 'rtl' ) ) {
						jQuery( this ).parent().css(
							{
								right: jQuery( this ).parent().css( 'left' ),
								left: 'auto'
							}
						);
					}
				},
				close: function ( event, ui ) {
					// When we're closing the dialog box we need to cancel the language change as we click on Cancel button.
					confirmDialog( 'no' );
				},
				buttons: [
					{
						text: __( 'OK', 'polylang' ),
						click: function ( event ) {
							confirmDialog( 'yes' );
						}
					},
					{
						text: __( 'Cancel', 'polylang' ),
						click: function ( event ) {
							confirmDialog( 'no' );
						}
					}
				]
			};

			if ( jQuery.ui.version >= '1.12.0' ) {
				Object.assign( dialogOptions, { classes: { 'ui-dialog': 'pll-confirmation-modal' } } );
			} else {
			Object.assign( dialogOptions, { dialogClass: 'pll-confirmation-modal' } ); // jQuery UI 1.11.4 - WP < 5.6
			}

			dialogContainer.dialog( dialogOptions );
		}
	);
	return { dialogContainer, dialogResult };
}

const initializeLanguageOldValue = () => {
	// Keep the old language value to be able to compare to the new one and revert to it if necessary.
	languagesList.attr( 'data-old-value', languagesList.children( ':selected' ).first().val() );
};

