/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 20
(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
var __webpack_unused_export__;
/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var f=__webpack_require__(677),k=Symbol.for("react.element"),l=Symbol.for("react.fragment"),m=Object.prototype.hasOwnProperty,n=f.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,p={key:!0,ref:!0,__self:!0,__source:!0};
function q(c,a,g){var b,d={},e=null,h=null;void 0!==g&&(e=""+g);void 0!==a.key&&(e=""+a.key);void 0!==a.ref&&(h=a.ref);for(b in a)m.call(a,b)&&!p.hasOwnProperty(b)&&(d[b]=a[b]);if(c&&c.defaultProps)for(b in a=c.defaultProps,a)void 0===d[b]&&(d[b]=a[b]);return{$$typeof:k,type:c,key:e,ref:h,props:d,_owner:n.current}}__webpack_unused_export__=l;exports.jsx=q;exports.jsxs=q;


/***/ },

/***/ 848
(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


if (true) {
  module.exports = __webpack_require__(20);
} else // removed by dead control flow
{}


/***/ },

/***/ 677
(module) {

module.exports = (function() { return this["React"]; }());

/***/ },

/***/ 419
(module) {

module.exports = (function() { return this["lodash"]; }());

/***/ },

/***/ 631
(module) {

module.exports = (function() { return this["wp"]["apiFetch"]; }());

/***/ },

/***/ 89
(module) {

module.exports = (function() { return this["wp"]["blockEditor"]; }());

/***/ },

/***/ 959
(module) {

module.exports = (function() { return this["wp"]["components"]; }());

/***/ },

/***/ 897
(module) {

module.exports = (function() { return this["wp"]["compose"]; }());

/***/ },

/***/ 987
(module) {

module.exports = (function() { return this["wp"]["data"]; }());

/***/ },

/***/ 601
(module) {

module.exports = (function() { return this["wp"]["element"]; }());

/***/ },

/***/ 873
(module) {

module.exports = (function() { return this["wp"]["hooks"]; }());

/***/ },

/***/ 75
(module) {

module.exports = (function() { return this["wp"]["i18n"]; }());

/***/ },

/***/ 933
(module) {

module.exports = (function() { return this["wp"]["primitives"]; }());

/***/ },

/***/ 172
(module) {

module.exports = (function() { return this["wp"]["url"]; }());

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
;// ./node_modules/@wpsyntex/polylang-react-library/build/middlewares/filter-path.js
/**
 * Filters requests for translatable entities.
 * This logic is shared across all Polylang plugins.
 *
 * @since 3.5
 *
 * @param {Object}   options        - API fetch options object.
 * @param {Array}    filteredRoutes - Array of route paths to filter.
 * @param {Function} filter         - Function to filter matching routes.
 * @return {Object} Modified REST request options.
 */
const filterPathMiddleware = (options, filteredRoutes, filter) => {
  const cleanPath = options.path.split('?')[0].replace(/^\/+|\/+$/g, ''); // Get path without query parameters and trim '/'.

  return Object.values(filteredRoutes).find(path => cleanPath === path) ? filter(options) : options;
};
/* harmony default export */ const filter_path = (filterPathMiddleware);
;// ./node_modules/@wpsyntex/polylang-react-library/build/middlewares/editors-requests-filter.js
/**
 * WordPress dependencies.
 */


/*
 * Internal dependencies.
 */


/**
 * Safely filters requests for translatable entities in block editor type screens.
 * Ensures that `pllFilteredRoutes` has been well defined on server side and
 * that the filtered request is a REST one.
 *
 * @param {Function} filterCallback - Function to filter API fetch options.
 */
const editorsRequestsFilter = filterCallback => {
  external_this_wp_apiFetch_default().use((options, next) => {
    /*
     * If options.url is defined, this is not a REST request but a direct call to post.php for legacy metaboxes.
     * If `filteredRoutes` is not defined, return early.
     */
    if ('undefined' !== typeof options.url || 'undefined' === typeof window.pllFilteredRoutes) {
      return next(options);
    }
    return next(filter_path(options, window.pllFilteredRoutes, filterCallback));
  });
};
/* harmony default export */ const editors_requests_filter = (editorsRequestsFilter);
// EXTERNAL MODULE: external {"this":["wp","data"]}
var external_this_wp_data_ = __webpack_require__(987);
// EXTERNAL MODULE: external {"this":["wp","url"]}
var external_this_wp_url_ = __webpack_require__(172);
// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(419);
;// ./js/src/editors/common/settings.js
/**
 * Module Constants
 */

const MODULE_KEY = 'pll/metabox';
const MODULE_CORE_EDITOR_KEY = 'core/editor';
const MODULE_SITE_EDITOR_KEY = 'core/edit-site';
const MODULE_POST_EDITOR_KEY = 'core/edit-post';
const MODULE_CORE_KEY = 'core';
const DEFAULT_STATE = {
  languages: [],
  selectedLanguage: {},
  translatedPosts: {},
  fromPost: null,
  currentTemplatePart: {}
};
const UNTRANSLATABLE_POST_TYPE = (/* unused pure expression or super */ null && (['wp_template', 'wp_global_styles']));
const POST_TYPE_WITH_TRASH = (/* unused pure expression or super */ null && (['page']));
const TEMPLATE_PART_SLUG_SEPARATOR = '___'; // Its value must be synchronized with its equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR.
const TEMPLATE_PART_SLUG_CHECK_LANGUAGE_PATTERN = '[a-z][a-z0-9_-]*'; // Its value must be synchronized with it equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR.

;// ./js/src/editors/common/store/utils.js
/* unused harmony import specifier */ var utils_select;
/* unused harmony import specifier */ var subscribe;
/* unused harmony import specifier */ var dispatch;
/* unused harmony import specifier */ var isNil;
/* unused harmony import specifier */ var isEmpty;
/* unused harmony import specifier */ var getSearchParams;
/* unused harmony import specifier */ var utils_MODULE_CORE_EDITOR_KEY;
/* unused harmony import specifier */ var utils_MODULE_KEY;
/* unused harmony import specifier */ var utils_MODULE_CORE_KEY;
/* unused harmony import specifier */ var utils_MODULE_SITE_EDITOR_KEY;
/**
 * WordPress Dependencies
 */



/**
 * Internal dependencies
 */



/**
 * Wait for the whole post block editor context has been initialized: current post loaded and languages list initialized.
 */
const isBlockPostEditorContextInitialized = () => {
  if (isNil(utils_select(utils_MODULE_CORE_EDITOR_KEY))) {
    return Promise.reject("Polylang languages panel can't be initialized because block editor isn't fully initialized.");
  }

  // save url params especially when a new translation is creating
  saveURLParams();

  /**
   * Set a promise fulfilled with the current post.
   */
  const isCurrentPostLoaded = new Promise(function (resolve) {
    const unsubscribe = subscribe(function () {
      const currentPost = utils_select(utils_MODULE_CORE_EDITOR_KEY).getCurrentPost();
      if (!isEmpty(currentPost)) {
        unsubscribe();
        resolve(currentPost);
      }
    });
  });

  /**
   * Set a promise fulfilled with the source post when a new draft is created from a source, null otherwise.
   */
  const isFromPostLoaded = new Promise(function (resolve) {
    const unsubscribe = subscribe(function () {
      const fromPostUrlParams = utils_select(utils_MODULE_KEY).getFromPost();
      if (!fromPostUrlParams || !fromPostUrlParams.id || !fromPostUrlParams.postType) {
        unsubscribe();
        resolve(null);
        return;
      }
      const fromPost = utils_select(utils_MODULE_CORE_KEY).getEntityRecord('postType', fromPostUrlParams.postType, fromPostUrlParams.id, {
        context: 'view'
      } // Use 'view' context so translators can read posts they cannot edit.
      );
      if (fromPost && fromPost.id) {
        unsubscribe();
        resolve(fromPost);
      }
    });
  });
  return Promise.all([isCurrentPostLoaded, isFromPostLoaded, isLanguagesinitialized()]).then(function (resolvedValues) {
    const [currentPost, fromPost] = resolvedValues;

    // Force update translations when creating a draft from a source post.
    if (fromPost && fromPost.id && currentPost.lang) {
      dispatch(utils_MODULE_CORE_EDITOR_KEY).editPost({
        translations: {
          ...fromPost.translations,
          [currentPost.lang]: currentPost.id
        }
      });
    }
  });
};

/**
 * Wait for the whole site editor context to be initialized: current template loaded and languages list initialized.
 */
const isSiteEditorContextInitialized = () => {
  // save url params especially when a new translation is creating
  saveURLParams();

  /**
   * Set a promise to wait for the current template to be fully loaded before making other processes.
   * It allows to see if both Site Editor and Core stores are available (@see getCurrentPostFromDataStore()).
   */
  const isTemplatePartLoaded = new Promise(function (resolve) {
    const unsubscribe = subscribe(function () {
      const store = utils_select(utils_MODULE_SITE_EDITOR_KEY);
      if (store) {
        unsubscribe();
        resolve();
      }
    });
  });
  return Promise.all([isTemplatePartLoaded, isLanguagesinitialized()]);
};

/**
 * Returns a promise fulfilled when the languages list is correctly initialized before making other processes.
 *
 * @return {Promise} A promise fulfilled with the languages list.
 */
const isLanguagesinitialized = () => new Promise(function (resolve) {
  const unsubscribe = (0,external_this_wp_data_.subscribe)(function () {
    const languages = (0,external_this_wp_data_.select)(MODULE_KEY)?.getLanguages();
    if (languages?.size > 0) {
      unsubscribe();
      resolve(languages);
    }
  });
});

/**
 * Save query string parameters from URL. They could be needed after
 * They could be null if they does not exist
 */
function saveURLParams() {
  // Variable window.location.search isn't use directly
  // Function getSearchParams return an URLSearchParams object for manipulating each parameter
  // Each of them are sanitized below
  const searchParams = getSearchParams();
  const fromPost = searchParams && searchParams.get('from_post');
  const postType = searchParams && searchParams.get('post_type');
  const newLanguage = searchParams && searchParams.get('new_lang');
  if (fromPost && postType && newLanguage) {
    dispatch(utils_MODULE_KEY).setFromPost({
      id: wp.sanitize.stripTagsAndEncodeText(fromPost),
      postType: wp.sanitize.stripTagsAndEncodeText(postType),
      newLanguage: wp.sanitize.stripTagsAndEncodeText(newLanguage)
    });
  }
}

/**
 * Gets the current post using the Site Editor store and the Core store.
 *
 * @return {object|null} The current post object, `null` if none found.
 */
const getCurrentPostFromDataStore = () => {
  const siteEditorSelector = utils_select(utils_MODULE_SITE_EDITOR_KEY);

  /**
   * Return null when called from our apiFetch middleware without a properly loaded store.
   */
  if (!siteEditorSelector) {
    return null;
  }
  const context = siteEditorSelector.getEditedPostContext();
  const editedContext = context?.postType && context?.postId ? context : {
    postId: siteEditorSelector.getEditedPostId(),
    postType: siteEditorSelector.getEditedPostType()
  };
  return null === editedContext ? null : utils_select(utils_MODULE_CORE_KEY).getEntityRecord('postType', editedContext.postType, editedContext.postId);
};
;// ./js/src/editors/common/utils.js
/* unused harmony import specifier */ var common_utils_select;
/* unused harmony import specifier */ var addQueryArgs;
/* unused harmony import specifier */ var isBoolean;
/* unused harmony import specifier */ var find;
/* unused harmony import specifier */ var utils_isEmpty;
/* unused harmony import specifier */ var utils_isNil;
/* unused harmony import specifier */ var map;
/* unused harmony import specifier */ var property;
/* unused harmony import specifier */ var escapeRegExp;
/* unused harmony import specifier */ var isUndefined;
/* unused harmony import specifier */ var common_utils_MODULE_KEY;
/* unused harmony import specifier */ var utils_MODULE_POST_EDITOR_KEY;
/* unused harmony import specifier */ var common_utils_MODULE_SITE_EDITOR_KEY;
/* unused harmony import specifier */ var common_utils_MODULE_CORE_EDITOR_KEY;
/* unused harmony import specifier */ var utils_TEMPLATE_PART_SLUG_SEPARATOR;
/* unused harmony import specifier */ var utils_getCurrentPostFromDataStore;
/**
 * WordPress Dependencies
 */




/**
 * Internal dependencies
 */



/**
 * Converts array of object to a map.
 *
 * @param {Array} array Array to convert.
 * @param {*}     key   The key in the object used as key to build the map.
 * @return {Map} Converted array.
 */
function convertArrayToMap(array, key) {
  const arrayMap = new Map();
  array.reduce(function (accumulator, currentValue) {
    accumulator.set(currentValue[key], currentValue);
    return accumulator;
  }, arrayMap);
  return arrayMap;
}

/**
 * Converts map to an associative array.
 *
 * @param {Map} mapToConvert The map to convert.
 * @return {Object} Converted map.
 */
function convertMapToObject(mapToConvert) {
  const object = {};
  mapToConvert.forEach(function (value, key) {
    this[key] = isBoolean(value) ? value.toString() : value;
  }, object);
  return object;
}

/**
 * Checks whether the current screen is the site editor.
 * Takes in account if Gutenberg is activated.
 *
 * @return {boolean} True if site editor screen, false otherwise.
 */
function isSiteBlockEditor() {
  return !!(document.getElementById('site-editor') || document.getElementById('edit-site-editor'));
}

/**
 * Returns the post type URL for REST API calls or undefined if the user hasn't the rights.
 *
 * @param {string} name The post type name.
 * @return {string|undefined} URL of the given post type, undefined if not available.
 */
function getPostsUrl(name) {
  const postTypes = common_utils_select('core').getEntitiesConfig('postType');
  const postType = find(postTypes, {
    name
  });
  return postType?.baseURL;
}

/**
 * Gets all query string parameters and convert them in a URLSearchParams object.
 *
 * @return {URLSearchParams|null} Search parameters object, null if none.
 */
function utils_getSearchParams() {
  // Variable window.location.search is just read for creating and returning a URLSearchParams object to be able to manipulate it more easily.
  // eslint-disable-next-line prettier/prettier
  if (!utils_isEmpty(window.location.search)) {
    // phpcs:ignore WordPressVIPMinimum.JS.Window.location
    return new URLSearchParams(window.location.search); // phpcs:ignore WordPressVIPMinimum.JS.Window.location
  }
  return null;
}

/**
 * Gets selected language.
 *
 * @param {string} lang The post language code.
 * @return {Object|null} The selected language, null otherwise.
 */
function getSelectedLanguage(lang) {
  const languages = common_utils_select(common_utils_MODULE_KEY).getLanguages();
  // Pick up this language as selected in languages list
  if (languages) {
    return languages.get(lang);
  }
  return null;
}

/**
 * Gets the default language.
 *
 * @return {Object} The default Language.
 */
function getDefaultLanguage() {
  const languages = common_utils_select(common_utils_MODULE_KEY).getLanguages();
  return Array.from(languages.values()).find(lang => lang.is_default);
}

/**
 * Checks if the given language is the default one.
 *
 * @param {string} lang The language code to compare with.
 * @return {boolean} True if the given language is the default one.
 */
function isDefaultLanguage(lang) {
  return lang === getDefaultLanguage().slug;
}

/**
 * Checks if the given request is for saving.
 *
 * @param {Object} options The initial request.
 * @return {boolean} True if the request is for saving.
 */
function isSaveRequest(options) {
  // If data is defined we are in a PUT or POST request method otherwise a GET request method
  // Test options.method property isn't efficient because most of REST request which use fetch API doesn't pass this property.
  // So, test options.data is necessary to know if the REST request is to save data.
  // However test if options.data is undefined isn't sufficient because some REST request pass a null value as the ServerSideRender Gutenberg component.
  if (!utils_isNil(options.data)) {
    return true;
  }
  return false;
}

/**
 * Checks if the given request concerns the current post type.
 *
 * Useful when saving a reusable block contained in another post type.
 * Indeed a reusable block is also a post, but its saving request doesn't concern the post currently edited.
 * As we don't know the language of the reusable block when the user triggers the reusable block saving action,
 * we need to pass the current post language to be sure that the reusable block will have a language.
 *
 * @see https://github.com/polylang/polylang/issues/437 - Reusable block has no language when it's saved from another post type editing.
 *
 * @param {Object} options the initial request
 * @return {boolean} True if the request concerns the current post.
 */
function isCurrentPostRequest(options) {
  // Saving translation data is needed only for all post types.
  // It's done by verifying options.path matches with one of baseURL of all post types
  // and compare current post id with this sent in the request.

  // List of post type baseURLs.
  const postTypeURLs = map(common_utils_select('core').getEntitiesConfig('postType'), property('baseURL'));

  // Id from the post currently edited.
  const postId = common_utils_select('core/editor').getCurrentPostId();

  // Id from the REST request.
  // options.data never isNil here because it's already verified before in isSaveRequest() function.
  const id = options.data.id;

  // Return true
  // if REST request baseURL matches with one of the known post type baseURLs
  // and the id from the post currently edited corresponds on the id passed to the REST request
  // Return false otherwise
  return -1 !== postTypeURLs.findIndex(function (element) {
    return new RegExp(`${escapeRegExp(element)}`).test(options.path);
  }) && postId === id;
}

/**
 * Checks if the given REST request is for the creation of a new template part translation.
 *
 * @param {Object} options The initial request.
 * @return {boolean} True if the request concerns a template part translation creation.
 */
function isTemplatePartTranslationCreationRequest(options) {
  return 'POST' === options.method && options.path.match(/^\/wp\/v2\/template-parts(?:\/|\?|$)/) && !utils_isNil(options.data.from_post) && !utils_isNil(options.data.lang);
}

/**
 * Checks if the given REST request is for the creation of a new template part.
 *
 * @param {Object} options The initial request.
 * @return {boolean} True if the request concerns a template part creation.
 */
function isNewTemplatePartCreationRequest(options) {
  return 'POST' === options.method && options.path.match(/^\/wp\/v2\/template-parts(?:\/|\?|$)/) && utils_isNil(options.data.from_post) && utils_isNil(options.data.lang);
}

/**
 * Adds language as query string parameter to the given request.
 *
 * @param {Object} options         The initial request.
 * @param {string} currentLanguage The language code to add to the request.
 */
function addLanguageToRequest(options, currentLanguage) {
  const hasLangArg = (0,external_this_wp_url_.hasQueryArg)(options.path, 'lang');
  const filterLang = (0,external_lodash_.isUndefined)(options.filterLang) || options.filterLang;
  if (filterLang && !hasLangArg) {
    options.path = (0,external_this_wp_url_.addQueryArgs)(options.path, {
      lang: currentLanguage
    });
  }
}

/**
 * Adds `include_untranslated` parameter to the request.
 *
 * @param {Object} options The initial request.
 * @return {void}
 */
function addIncludeUntranslatedParam(options) {
  options.path = addQueryArgs(options.path, {
    include_untranslated: true
  });
}

/**
 * Use addIncludeUntranslatedParam if the given page is a template part page.
 * Or if the template editing mode is enabled inside post editing.
 *
 * @param {Object} options The initial request.
 * @return {void}
 */
function maybeRequireIncludeUntranslatedTemplate(options) {
  const params = new URL(document.location).searchParams;
  const postType = params.get('postType');
  const postId = params.get('postId');
  const isEditingTemplate = common_utils_select(utils_MODULE_POST_EDITOR_KEY)?.isEditingTemplate();
  if ('wp_template_part' === postType && !utils_isNil(postId) || isEditingTemplate) {
    addIncludeUntranslatedParam(options);
  }
}

/**
 * Returns true if the given post is a template part, false otherwise.
 *
 * @param {Object} post A post object.
 * @return {boolean} Whether it is a template part or not.
 */
function isTemplatePart(post) {
  return 'wp_template_part' === post.type;
}

/**
 * Returns the current post type considering the Site Editor or Post Editor.
 *
 * @return {string} The current post type.
 */
function getCurrentPostType() {
  if (isSiteBlockEditor()) {
    return common_utils_select(common_utils_MODULE_SITE_EDITOR_KEY).getEditedPostType();
  }
  return common_utils_select(common_utils_MODULE_CORE_EDITOR_KEY).getCurrentPostType();
}

/**
 * Adds parameters according to the context of the request.
 *
 * @since 3.5
 *
 * @param {APIFetchOptions} options The options of the request.
 * @return {APIFetchOptions} The modified options of the request.
 */
function addParametersToRequest(options) {
  const currentLangSlug = getCurrentLanguageSlug();

  // `POST` or `PUT` request.
  if (isSaveRequest(options)) {
    /**
     * Use default language for new template part that doesn't exist in any language,
     * otherwise use the current language.
     */
    if (isNewTemplatePartCreationRequest(options)) {
      addLanguageToRequest(options, getDefaultLanguage()?.slug);
    }
    if (!isCurrentPostRequest(options) && !isTemplatePartTranslationCreationRequest(options)) {
      addLanguageToRequest(options, currentLangSlug);
    }
    maybeAddLangSuffixToTemplatePart(options, currentLangSlug);
    return options;
  }
  const currentPostType = getCurrentPostType();

  // Current language is set to default when editing templates.
  if ('wp_template' === currentPostType) {
    addLanguageToRequest(options, getDefaultLanguage()?.slug);
  }
  const templatePartListRegex = new RegExp(/^\/wp\/v2\/template-parts\/?(?:\?.*)?$/);

  // Template part list request.
  if (templatePartListRegex.test(options.path)) {
    maybeRequireIncludeUntranslatedTemplate(options);
  }

  // All kinds of requests.
  addLanguageToRequest(options, currentLangSlug);
  return options;
}

/**
 * Gets language from store or a fallback javascript global variable.
 *
 * @return {string} The language slug.
 */
function getCurrentLanguageSlug() {
  if (isUndefined(common_utils_select(common_utils_MODULE_CORE_EDITOR_KEY))) {
    // Return ASAP to avoid issues later.
    return pll_block_editor_plugin_settings.lang.slug;
  }

  // Post block editor case.
  const postLanguage = common_utils_select(common_utils_MODULE_CORE_EDITOR_KEY).getEditedPostAttribute('lang');
  if (!isUndefined(postLanguage) && postLanguage) {
    return postLanguage;
  }

  // Returns the default lang if the current location is a template part list
  // and update pll_block_editor_plugin_settings at the same time.
  const params = new URL(document.location).searchParams;
  const postType = params.get('postType');
  const postId = params.get('postId');
  if ('wp_template_part' === postType && utils_isNil(postId)) {
    pll_block_editor_plugin_settings.lang = getDefaultLanguage();
    return pll_block_editor_plugin_settings.lang.slug;
  }

  // FSE template editor case.
  const template = utils_getCurrentPostFromDataStore();
  const templateLanguage = template?.lang;
  if (!isUndefined(templateLanguage) && templateLanguage) {
    return templateLanguage;
  }

  // For the first requests block editor isn't initialized yet.
  // So language is retrieved from a javascript global variable initialized server-side.
  return pll_block_editor_plugin_settings.lang.slug;
}

/**
 * Adds the language suffix to a template part only during creation.
 *
 * @param {Object} options  Object representing a REST request.
 * @param {string} langSlug The Language slug to add.
 * @return {void}
 */
function maybeAddLangSuffixToTemplatePart(options, langSlug) {
  const restBaseUrl = getPostsUrl('wp_template_part');
  if (isUndefined(restBaseUrl)) {
    // The user hasn't the rights to edit template part.
    return;
  }
  const templatePartURLRegExp = new RegExp(escapeRegExp(restBaseUrl));
  if ('POST' === options.method && templatePartURLRegExp.test(options.path)) {
    const languages = common_utils_select(common_utils_MODULE_KEY).getLanguages();
    const language = languages.get(langSlug);
    if (!language.is_default) {
      // No suffix for default language.
      const langSuffix = utils_TEMPLATE_PART_SLUG_SEPARATOR + langSlug;
      options.data.slug += langSuffix;
    }
  }
}
// EXTERNAL MODULE: external {"this":["wp","i18n"]}
var external_this_wp_i18n_ = __webpack_require__(75);
// EXTERNAL MODULE: external {"this":["wp","compose"]}
var external_this_wp_compose_ = __webpack_require__(897);
// EXTERNAL MODULE: external {"this":["wp","hooks"]}
var external_this_wp_hooks_ = __webpack_require__(873);
// EXTERNAL MODULE: external {"this":["wp","element"]}
var external_this_wp_element_ = __webpack_require__(601);
// EXTERNAL MODULE: external {"this":["wp","blockEditor"]}
var external_this_wp_blockEditor_ = __webpack_require__(89);
// EXTERNAL MODULE: external {"this":["wp","components"]}
var external_this_wp_components_ = __webpack_require__(959);
// EXTERNAL MODULE: external {"this":["wp","primitives"]}
var external_this_wp_primitives_ = __webpack_require__(933);
// EXTERNAL MODULE: ./node_modules/react/jsx-runtime.js
var jsx_runtime = __webpack_require__(848);
;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/translation.js
/**
 * Translation icon - translation Dashicon.
 */

/**
 * WordPress dependencies
 */


const isPrimitivesComponents = 'undefined' !== typeof wp.primitives;
const translation = isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  width: "20",
  height: "20",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 20 20",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M11 7H9.49c-.63 0-1.25.3-1.59.7L7 5H4.13l-2.39 7h1.69l.74-2H7v4H2c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h7c1.1 0 2 .9 2 2v2zM6.51 9H4.49l1-2.93zM10 8h7c1.1 0 2 .9 2 2v7c0 1.1-.9 2-2 2h-7c-1.1 0-2-.9-2-2v-7c0-1.1.9-2 2-2zm7.25 5v-1.08h-3.17V9.75h-1.16v2.17H9.75V13h1.28c.11.85.56 1.85 1.28 2.62-.87.36-1.89.62-2.31.62-.01.02.22.97.2 1.46.84 0 2.21-.5 3.28-1.15 1.09.65 2.48 1.15 3.34 1.15-.02-.49.2-1.44.2-1.46-.43 0-1.49-.27-2.38-.63.7-.77 1.14-1.77 1.25-2.61h1.36zm-3.81 1.93c-.5-.46-.85-1.13-1.01-1.93h2.09c-.17.8-.51 1.47-1 1.93l-.04.03s-.03-.02-.04-.03z"
  })
}) : 'translation';
/* harmony default export */ const icons_translation = (translation);
;// ./js/src/editors/common/components/language-flag/index.js
/* eslint-disable import/no-extraneous-dependencies */
/**
 * External dependencies.
 */



/**
 * Displays a flag icon for a given language.
 *
 * @since 3.1
 * @since 3.2 Now its own component.
 *
 * @param {Object} props          LanguageFlag props.
 * @param {Object} props.language Language object for the flag.
 *
 * @return {React.ReactElement} Flag component.
 */

function LanguageFlag({
  language
}) {
  if (!(0,external_lodash_.isNil)(language)) {
    return !(0,external_lodash_.isEmpty)(language.flag_url) ? /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      className: "pll-select-flag",
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("img", {
        src: language.flag_url,
        alt: language.name,
        title: language.name,
        className: "flag"
      })
    }) : /*#__PURE__*/(0,jsx_runtime.jsxs)("abbr", {
      children: [language.slug, /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
        className: "screen-reader-text",
        children: language.name
      })]
    });
  }
  return /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
    className: "pll-translation-icon",
    children: icons_translation
  });
}
/* harmony default export */ const language_flag = (LanguageFlag);
;// ./js/src/editors/common/components/language-dropdown/index.js
/**
 * WordPress dependencies.
 */



/**
 * Internal dependencies.
 */


/**
 * Displays a dropdown to select a language.
 *
 * @since 3.1
 *
 * @param {Object}   props                  LanguageDropdown props.
 * @param {Function} props.handleChange     Callback to be executed when language changes.
 * @param {Object}   props.languages        An iterable object containing languages objects.
 * @param {Object}   props.selectedLanguage An object representing a Polylang Language. Default to null.
 * @param {string}   props.defaultValue     Value to be selected if the selected language is not provided. Default to an empty string.
 *
 * @return {Object} A dropdown selector for languages.
 */

function LanguageDropdown({
  handleChange,
  languages,
  selectedLanguage = null,
  defaultValue = ''
}) {
  const selectedLanguageSlug = selectedLanguage?.slug ? selectedLanguage.slug : defaultValue;
  const normalizedLanguagesForSelectControl = (0,external_this_wp_element_.useMemo)(() => {
    return Array.from(languages.values()).map(({
      slug,
      name
    }) => ({
      value: slug,
      label: name
    }));
  }, [languages]);
  return /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
    id: "pll-language-select-control",
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(language_flag, {
      language: selectedLanguage
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.SelectControl, {
      value: selectedLanguageSlug,
      onChange: newLangSlug => handleChange(newLangSlug),
      options: normalizedLanguagesForSelectControl,
      id: "pll_post_lang_choice",
      name: "pll_post_lang_choice",
      className: "post_lang_choice",
      __nextHasNoMarginBottom: true,
      __next40pxDefaultSize: true
    })]
  });
}

;// ./js/src/editors/common/store/index.js
/**
 * WordPress Dependencies
 */



/**
 * Internal dependencies
 */


const actions = {
  setLanguages(languages) {
    return {
      type: 'SET_LANGUAGES',
      languages
    };
  },
  setFromPost(fromPost) {
    return {
      type: 'SET_FROM_POST',
      fromPost
    };
  },
  fetchFromAPI(options) {
    return {
      type: 'FETCH_FROM_API',
      options
    };
  }
};
const store = (0,external_this_wp_data_.createReduxStore)(MODULE_KEY, {
  reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
      case 'SET_LANGUAGES':
        return {
          ...state,
          languages: action.languages
        };
      case 'SET_FROM_POST':
        return {
          ...state,
          fromPost: action.fromPost
        };
      case 'SET_CURRENT_TEMPLATE_PART':
        return {
          ...state,
          currentTemplatePart: action.currentTemplatePart
        };
      default:
        return state;
    }
  },
  selectors: {
    getLanguages(state) {
      return state.languages;
    },
    getFromPost(state) {
      return state.fromPost;
    }
  },
  actions,
  controls: {
    FETCH_FROM_API(action) {
      return external_this_wp_apiFetch_default()({
        ...action.options
      });
    }
  },
  resolvers: {
    *getLanguages() {
      const path = '/pll/v1/languages';
      const languages = yield actions.fetchFromAPI({
        path,
        filterLang: false
      });
      return actions.setLanguages(convertArrayToMap(languages, 'slug'));
    }
  }
});
(0,external_this_wp_data_.register)(store);
;// ./js/src/editors/widget/language-attribute-control.js
/**
 * Add blocks attributes
 */

/**
 * WordPress Dependencies
 */









/**
 * Internal dependencies
 */




/*
 * Loads Polylang Redux store, used for languages.
 */


const LanguageAttribute = {
  type: 'string',
  default: 'every'
};
const addLangChoiceAttribute = function (settings, name) {
  const unallowedBlockNames = ['core/widget-area', 'core/legacy-widget'];
  if (unallowedBlockNames.find(element => element === name)) {
    return settings;
  }
  settings.attributes = (0,external_lodash_.assign)(settings.attributes, {
    pll_lang: LanguageAttribute
  });
  return settings;
};
(0,external_this_wp_hooks_.addFilter)('blocks.registerBlockType', 'pll/lang-choice', addLangChoiceAttribute);

/**
 * Determines if the language control should be shown for a block.
 * Language control is only shown on outer blocks (without parents) that are language filterable (widget-area is excluded as it's just a container).
 *
 * @param {string}  clientId             Block client ID.
 * @param {boolean} isLanguageFilterable Whether the block is language filterable.
 * @return {boolean} True if language control should be shown.
 */
const useShouldShowLanguageControl = (clientId, isLanguageFilterable) => {
  return (0,external_this_wp_data_.useSelect)(selectStore => {
    if (!isLanguageFilterable) {
      return false;
    }
    const blockParents = selectStore(external_this_wp_blockEditor_.store).getBlockParents(clientId).filter(parentId => {
      const parentBlock = selectStore(external_this_wp_blockEditor_.store).getBlock(parentId);
      return parentBlock && parentBlock.name !== 'core/widget-area';
    });
    return blockParents.length === 0;
  }, [clientId, isLanguageFilterable]);
};
const withInspectorControls = (0,external_this_wp_compose_.createHigherOrderComponent)(BlockEdit => {
  return props => {
    const languages = (0,external_this_wp_data_.select)(MODULE_KEY).getLanguages();
    const {
      pll_lang
    } = props.attributes;
    const isLanguageFilterable = !(0,external_lodash_.isNil)(pll_lang);
    const selectedLanguage = languages.get(pll_lang);
    const withAllLanguages = languages.set('', {
      slug: 'every',
      name: (0,external_this_wp_i18n_.__)('All languages', 'polylang-pro')
    });
    const shouldShowLanguageControl = useShouldShowLanguageControl(props.clientId, isLanguageFilterable);
    return /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_element_.Fragment, {
      children: [/*#__PURE__*/(0,jsx_runtime.jsx)(BlockEdit, {
        ...props
      }), shouldShowLanguageControl && /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_blockEditor_.InspectorControls, {
        children: /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_components_.PanelBody, {
          title: (0,external_this_wp_i18n_.__)('Languages', 'polylang-pro'),
          children: [/*#__PURE__*/(0,jsx_runtime.jsx)("label", {
            htmlFor: "pll_post_lang_choice",
            children: (0,external_this_wp_i18n_.__)('The block is displayed for:', 'polylang-pro')
          }), /*#__PURE__*/(0,jsx_runtime.jsx)(LanguageDropdown, {
            selectedLanguage: selectedLanguage,
            handleChange: nextLangSlug => {
              props.setAttributes({
                pll_lang: nextLangSlug
              });
            },
            defaultValue: LanguageAttribute.default,
            languages: withAllLanguages
          })]
        })
      })]
    });
  };
}, 'withInspectorControl');
isLanguagesinitialized().then(function () {
  (0,external_this_wp_hooks_.addFilter)('editor.BlockEdit', 'pll/lang-choice-with-inspector-controls', withInspectorControls);
});
;// ./js/src/editors/widget/index.js
/**
 * External dependencies
 */


/**
 * Internal dependencies.
 */


/*
 * Loads language attribute feature for widget blocks.
 */

editors_requests_filter(
/**
 * Adds default language parameter to a given request.
 *
 * @since 3.5
 *
 * @param {APIFetchOptions} options REST request options.
 * @return {APIFetchOptions} Modified REST request options.
 */
function (options) {
  if ('undefined' !== typeof pll_block_editor_plugin_settings) {
    addLanguageToRequest(options, pll_block_editor_plugin_settings.lang.slug);
  }
  return options;
});
})();

this["polylang-pro"] = __webpack_exports__;
/******/ })()
;