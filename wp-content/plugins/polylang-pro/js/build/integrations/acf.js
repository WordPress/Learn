/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 631
(module) {

module.exports = (function() { return this["wp"]["apiFetch"]; }());

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";

// EXTERNAL MODULE: external {"this":["wp","apiFetch"]}
var external_this_wp_apiFetch_ = __webpack_require__(631);
var external_this_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_this_wp_apiFetch_);
;// ./src/integrations/ACF/js/lib/object.js
/**
 * Gets the keys of existing relational ACF fields in the DOM (`relationship`, `post_object` and `taxonomy`).
 *
 * @return {[]} An array of relational fields.
 */
function getRelationalFieldKeys() {
  const fields = [];

  // Adds relationship, post object and taxonomy fields to the fields to be refreshed.
  document.querySelectorAll('.acf-field-relationship, .acf-field-post-object, .acf-field-taxonomy').forEach(function (relationshipField) {
    const field = relationshipField.getAttribute('data-key');
    fields.push(field);
  });
  return fields;
}

/**
 * Updates the ACF field with the content translated into the new language selected in the switcher.
 *
 * @param {Object} fieldData ACF field data fetched using AJAX (key and content).
 */
function reloadField(fieldData) {
  // Data comes from ACF field and server side.
  const field = document.querySelector('.acf-' + fieldData.field_key);
  field.outerHTML = fieldData.field_data;

  /**
   * Triggers the action once the field has been refreshed,
   * so that the actions hooked previously are re-launched on the newly refreshed field.
   */
  acf.do_action('ready_field/type=' + field.getAttribute('data-type'), field);
}

/**
 * Refreshes `relationship` relational fields.
 * We need to reload the choices list for relationship fields (otherwise it remains empty).
 *
 * @param {Array} relationshipFields The `relationship` fields.
 */
function refreshRelationShipFields(relationshipFields) {
  relationshipFields.forEach(function (field) {
    field.fetch();
  });
}

/**
 * Gets the `post_object` and `relationship` relational fields.
 * In addition, `acf.getFields` reloads the list of posts in `post_object` fields.
 *
 * @return {{postObject, relationShip}} `post_object` and `relationship` relational fields.
 */
function getRelationalFields() {
  const relationshipFields = acf.getFields({
    type: 'relationship'
  });

  // Reloads the posts list in `post_object` fields.
  const postObjectFields = acf.getFields({
    type: 'post_object'
  });
  return {
    relationShip: relationshipFields,
    postObject: postObjectFields
  };
}
;// ./src/integrations/ACF/js/lib/post.js
/**
 * WordPress dependencies.
 */


/**
 * Internal dependencies
 */


/**
 * Refreshes relational fields when switching languages on a post.
 *
 * @param {CustomEvent} e The event.
 */
function onPostLangChoice(e) {
  const fieldKeys = getRelationalFieldKeys();
  if (0 === fieldKeys.length) {
    return;
  }
  const postId = document.getElementById('post_ID').getAttribute('value');
  let nonce = document.querySelector('#_pll_nonce')?.value; // Classic editor.
  if (undefined === nonce) {
    // Block editor.
    nonce = pll_block_editor_plugin_settings.nonce;
  }
  const data = new FormData();
  data.set('action', 'acf_post_lang_choice');
  data.set('lang', encodeURI(e.detail.lang.slug));
  data.set('fields', fieldKeys);
  data.set('post_id', postId);
  data.set('_pll_nonce', nonce);
  external_this_wp_apiFetch_default()({
    url: ajaxurl,
    method: 'POST',
    body: data
  }).then(response => {
    response.fields.forEach(function (res) {
      reloadField(res);
    });
    const fields = getRelationalFields();
    refreshRelationShipFields(fields.relationShip);
  });
}
;// ./src/integrations/ACF/js/lib/term.js
/**
 * WordPress dependencies.
 */


/**
 * Internal dependencies
 */


/**
 * Returns the selected language slug.
 *
 * @return {string} The selected language slug.
 */
function getSelectedLanguageSlug() {
  const selectLang = document.querySelector('#term_lang_choice');
  return JSON.parse(selectLang.options.item(selectLang.selectedIndex).getAttribute('data-lang')).slug;
}

/**
 * Refreshes relational fields according to the selected language in the term creation page.
 */
function onTermCreationPageLoad() {
  /* global adminpage */
  if ('edit-tags-php' === adminpage) {
    /**
     * When we're on a term creation page, we don't have a current language, so relational fields are loaded in all languages.
     * We need to add the language slug fetched from the language selector to the AJAX request
     * to load the relational field with the correct current language.
     */
    acf.addFilter('relationship_ajax_data', function (param) {
      param.lang = getSelectedLanguageSlug();
      return param;
    });
    acf.addFilter('select2_ajax_data', function (param) {
      // Post object.
      param.lang = getSelectedLanguageSlug();
      return param;
    });
  }
}

/**
 * Refreshes relational fields when switching languages on a term.
 *
 * @param {CustomEvent} e The event.
 */
function onTermLangChoice(e) {
  const fieldKeys = getRelationalFieldKeys();
  if (0 === fieldKeys.length) {
    return;
  }
  const termID = document.querySelector("input[name='tag_ID']") ? document.querySelector("input[name='tag_ID']").value : '0';
  let nonce = document.querySelector('#_pll_nonce')?.value; // Classic editor.
  if (undefined === nonce) {
    // Block editor.
    nonce = pll_block_editor_plugin_settings.nonce;
  }
  const taxonomy = new URLSearchParams(wp.sanitize.stripTags(window.location.search) // phpcs:ignore WordPressVIPMinimum.JS.Window.location
  ).get('taxonomy');
  const data = new FormData();
  data.set('action', 'acf_term_lang_choice');
  data.set('lang', encodeURI(e.detail.lang.slug));
  data.set('fields', fieldKeys);
  data.set('term_id', termID);
  data.set('_pll_nonce', nonce);
  data.set('taxonomy', taxonomy);
  external_this_wp_apiFetch_default()({
    url: ajaxurl,
    method: 'POST',
    body: data
  }).then(response => {
    response.fields.forEach(function (res) {
      reloadField(res);
    });
    const fields = getRelationalFields();
    if (fields.relationShip) {
      /**
       * Since the language is not saved when switching languages on a term, we need to add this `lang` parameter
       * to the AJAX request to load the relational field with the correct current language.
       */
      acf.addFilter('relationship_ajax_data', function (param) {
        param.lang = response.lang;
        return param;
      });
    }
    if (fields.postObject) {
      /**
       * Since the language is not saved when switching languages on a term, we need to add this `lang` parameter
       * to the AJAX request to load the relational field with the correct current language.
       */
      acf.addFilter('select2_ajax_data', function (param) {
        param.lang = response.lang;
        return param;
      });
    }
    refreshRelationShipFields(fields.relationShip);
  });
}
;// ./src/integrations/ACF/js/index.js
/**
 * Internal dependencies.
 */



/**
 * Posts.
 */
document.addEventListener('onPostLangChoice', onPostLangChoice);

/**
 * Terms.
 */
document.addEventListener('DOMContentLoaded', onTermCreationPageLoad);
document.addEventListener('onTermLangChoice', onTermLangChoice);
})();

this["polylang-pro"] = __webpack_exports__;
/******/ })()
;