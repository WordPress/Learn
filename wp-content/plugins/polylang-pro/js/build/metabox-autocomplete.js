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
/* unused harmony export initMetaboxAutoComplete */
/**
 * @package Polylang
 */

// Translations autocomplete input box.
function initMetaboxAutoComplete() {
	jQuery('.tr_lang').each(
		function () {
			var tr_lang = jQuery(this).attr('id').substring(8);
			var td = jQuery(this).parent().parent().siblings('.pll-edit-column');

			jQuery(this).autocomplete(
				{
					minLength: 0,
					source: ajaxurl + '?action=pll_posts_not_translated' +
						'&post_language=' + jQuery('.post_lang_choice').val() +
						'&translation_language=' + tr_lang +
						'&post_type=' + jQuery('#post_type').val() +
						'&_pll_nonce=' + jQuery('#_pll_nonce').val(),
					select: function (event, ui) {
						jQuery('#htr_lang_' + tr_lang).val(ui.item.id);
						// ui.item.link is built and come from server side and is well escaped when necessary
						td.html(ui.item.link); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
					},
				}
			);

			// when the input box is emptied
			jQuery(this).on(
				'blur',
				function () {
					if ( ! jQuery(this).val()  ) {
						jQuery('#htr_lang_' + tr_lang).val(0);
						// Value is retrieved from HTML already generated server side
						td.html(td.siblings('.hidden').children().clone()); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
					}
				}
			);
		}
	);
}

