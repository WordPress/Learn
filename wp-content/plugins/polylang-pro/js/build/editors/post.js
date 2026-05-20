/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 20
(__unused_webpack_module, exports, __webpack_require__) {

"use strict";
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
function q(c,a,g){var b,d={},e=null,h=null;void 0!==g&&(e=""+g);void 0!==a.key&&(e=""+a.key);void 0!==a.ref&&(h=a.ref);for(b in a)m.call(a,b)&&!p.hasOwnProperty(b)&&(d[b]=a[b]);if(c&&c.defaultProps)for(b in a=c.defaultProps,a)void 0===d[b]&&(d[b]=a[b]);return{$$typeof:k,type:c,key:e,ref:h,props:d,_owner:n.current}}exports.Fragment=l;exports.jsx=q;exports.jsxs=q;


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

/***/ 959
(module) {

module.exports = (function() { return this["wp"]["components"]; }());

/***/ },

/***/ 488
(module) {

module.exports = (function() { return this["wp"]["coreData"]; }());

/***/ },

/***/ 987
(module) {

module.exports = (function() { return this["wp"]["data"]; }());

/***/ },

/***/ 53
(module) {

module.exports = (function() { return this["wp"]["editPost"]; }());

/***/ },

/***/ 2
(module) {

module.exports = (function() { return this["wp"]["editor"]; }());

/***/ },

/***/ 601
(module) {

module.exports = (function() { return this["wp"]["element"]; }());

/***/ },

/***/ 989
(module) {

module.exports = (function() { return this["wp"]["htmlEntities"]; }());

/***/ },

/***/ 75
(module) {

module.exports = (function() { return this["wp"]["i18n"]; }());

/***/ },

/***/ 672
(module) {

module.exports = (function() { return this["wp"]["notices"]; }());

/***/ },

/***/ 125
(module) {

module.exports = (function() { return this["wp"]["plugins"]; }());

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
// EXTERNAL MODULE: external {"this":["wp","editPost"]}
var external_this_wp_editPost_ = __webpack_require__(53);
// EXTERNAL MODULE: external {"this":["wp","element"]}
var external_this_wp_element_ = __webpack_require__(601);
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
// EXTERNAL MODULE: external {"this":["wp","plugins"]}
var external_this_wp_plugins_ = __webpack_require__(125);
;// ./js/src/editors/common/app.js
/**
 * External dependencies
 */


/**
 * WordPress Dependencies.
 */


const App = ({
  sidebar,
  sidebarName,
  onPromise,
  children
}) => {
  onPromise().then(() => {
    (0,external_this_wp_plugins_.registerPlugin)(sidebarName, {
      icon: icons_translation,
      render: sidebar
    });
  }, reason => {
    console.info(reason); // eslint-disable-line no-console
  });
  return /*#__PURE__*/(0,jsx_runtime.jsx)(jsx_runtime.Fragment, {
    children: children
  });
};
/* harmony default export */ const app = (App);
// EXTERNAL MODULE: external {"this":["wp","data"]}
var external_this_wp_data_ = __webpack_require__(987);
// EXTERNAL MODULE: external {"this":["wp","notices"]}
var external_this_wp_notices_ = __webpack_require__(672);
;// ./js/src/editors/common/components/display-notices/index.js
/**
 * WordPress Dependencies.
 */


const {
  stripTags
} = wp.sanitize;
const DisplayNotices = ({
  notices
}) => {
  const {
    createErrorNotice,
    createInfoNotice,
    createSuccessNotice,
    createWarningNotice
  } = (0,external_this_wp_data_.useDispatch)(external_this_wp_notices_.store);
  if (!notices) {
    return null;
  }
  notices.forEach(notice => {
    const noticeOptions = {
      type: 'snackbar',
      explicitDismiss: true
    };
    const message = stripTags(notice.message);
    switch (notice.type) {
      case 'error':
        createErrorNotice(message, noticeOptions);
        break;
      case 'info':
        createInfoNotice(message, noticeOptions);
        break;
      case 'success':
        createSuccessNotice(message, noticeOptions);
        break;
      case 'warning':
        createWarningNotice(message, noticeOptions);
        break;
    }
  });

  /**
   * Renderless component.
   */
  return null;
};
/* harmony default export */ const display_notices = (DisplayNotices);
// EXTERNAL MODULE: external {"this":["wp","i18n"]}
var external_this_wp_i18n_ = __webpack_require__(75);
;// ./js/src/editors/common/components/sidebar/index.js
/**
 * WordPress dependencies
 */


const Sidebar = ({
  SidebarSlot,
  MoreMenuItemSlot,
  sidebarName,
  children
}) => {
  const title = (0,external_this_wp_i18n_.__)('Languages', 'polylang-pro');
  return /*#__PURE__*/(0,jsx_runtime.jsxs)(jsx_runtime.Fragment, {
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(SidebarSlot, {
      name: sidebarName,
      title: title,
      children: children
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(MoreMenuItemSlot, {
      target: sidebarName,
      children: title
    })]
  });
};
/* harmony default export */ const sidebar = (Sidebar);
// EXTERNAL MODULE: external {"this":["wp","url"]}
var external_this_wp_url_ = __webpack_require__(172);
// EXTERNAL MODULE: external {"this":["wp","coreData"]}
var external_this_wp_coreData_ = __webpack_require__(488);
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
const UNTRANSLATABLE_POST_TYPE = ['wp_template', 'wp_global_styles'];
const POST_TYPE_WITH_TRASH = (/* unused pure expression or super */ null && (['page']));
const TEMPLATE_PART_SLUG_SEPARATOR = '___'; // Its value must be synchronized with its equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR.
const TEMPLATE_PART_SLUG_CHECK_LANGUAGE_PATTERN = '[a-z][a-z0-9_-]*'; // Its value must be synchronized with it equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR.

// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(419);
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
// EXTERNAL MODULE: external {"this":["wp","components"]}
var external_this_wp_components_ = __webpack_require__(959);
;// ./js/src/editors/common/components/default-lang-icon/index.js
/* unused harmony import specifier */ var star;
/* unused harmony import specifier */ var Icon;
/* unused harmony import specifier */ var __;
/* unused harmony import specifier */ var _jsxs;
/* unused harmony import specifier */ var _Fragment;
/* unused harmony import specifier */ var _jsx;
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



const DefaultLangIcon = () => /*#__PURE__*/_jsxs(_Fragment, {
  children: [/*#__PURE__*/_jsx(Icon, {
    icon: star,
    className: "pll-default-lang-icon"
  }), /*#__PURE__*/_jsx("span", {
    className: "screen-reader-text",
    children: __('Default language.', 'polylang-pro')
  })]
});
/* harmony default export */ const default_lang_icon = ((/* unused pure expression or super */ null && (DefaultLangIcon)));
;// ./js/src/editors/common/components/language-item/index.js
/* unused harmony import specifier */ var useSelect;
/* unused harmony import specifier */ var language_item_;
/* unused harmony import specifier */ var language_item_MODULE_CORE_KEY;
/* unused harmony import specifier */ var language_item_LanguageFlag;
/* unused harmony import specifier */ var language_item_DefaultLangIcon;
/* unused harmony import specifier */ var language_item_jsxs;
/* unused harmony import specifier */ var language_item_Fragment;
/* unused harmony import specifier */ var language_item_jsx;
/**
 * WordPress dependencies
 */



/**
 * Internal dependencies
 */




const LanguageItem = ({
  language,
  currentPost
}) => {
  const postType = useSelect(select => select(language_item_MODULE_CORE_KEY).getPostType(currentPost.type), [currentPost]);
  return /*#__PURE__*/language_item_jsxs(language_item_Fragment, {
    children: [/*#__PURE__*/language_item_jsx("p", {
      children: /*#__PURE__*/language_item_jsx("strong", {
        children: language_item_('Language', 'polylang-pro')
      })
    }), /*#__PURE__*/language_item_jsxs("div", {
      className: "pll-language-item",
      children: [/*#__PURE__*/language_item_jsx(language_item_LanguageFlag, {
        language: language
      }), /*#__PURE__*/language_item_jsx("span", {
        className: "pll-language-name",
        children: language.name
      }), language.is_default && /*#__PURE__*/language_item_jsx(language_item_DefaultLangIcon, {})]
    }), language.is_default && /*#__PURE__*/language_item_jsx("div", {
      children: /*#__PURE__*/language_item_jsx("span", {
        className: "pll-metabox-info",
        children: 'wp_template_part' === postType?.slug ?? language_item_('This template part is used for languages that have not yet been translated.', 'polylang-pro')
      })
    })]
  });
};
/* harmony default export */ const language_item = ((/* unused pure expression or super */ null && (LanguageItem)));
;// ./js/src/editors/common/components/metaboxes/wrapper/index.js

const MetaboxWrapper = ({
  children
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
    className: "components-panel__body is-opened",
    children: /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
      className: "pll-metabox-location",
      children: children
    })
  });
};
/* harmony default export */ const wrapper = (MetaboxWrapper);
;// ./js/src/editors/common/components/not-translatable-notice/index.js
/**
 * WordPress Dependencies.
 */


const NotTranslatableNotice = ({
  postType
}) => {
  if ('wp_template' === postType) {
    return /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
      className: "pll-metabox-error components-notice is-warning",
      children: (0,external_this_wp_i18n_.__)('Templates are not translatable, only template parts are.', 'polylang-pro')
    });
  }
  return /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
    className: "pll-metabox-error components-notice is-warning",
    children: (0,external_this_wp_i18n_.__)('This entity is not translatable.', 'polylang-pro')
  });
};
/* harmony default export */ const not_translatable_notice = (NotTranslatableNotice);
;// ./js/src/editors/common/components/metaboxes/container/index.js
/**
 * WordPress Dependencies.
 */


/**
 * Internal Dependencies.
 */



const MetaboxContainer = ({
  isError,
  isAllowedPostType,
  postType,
  children
}) => {
  if (!isAllowedPostType) {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(wrapper, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(not_translatable_notice, {
        postType: postType
      })
    });
  }
  if (isError) {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(wrapper, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
        className: "pll-metabox-error components-notice is-error",
        children: (0,external_this_wp_i18n_.__)('Unable to retrieve the content language', 'polylang-pro')
      })
    });
  }
  return /*#__PURE__*/(0,jsx_runtime.jsx)(wrapper, {
    children: children
  });
};
/* harmony default export */ const container = (MetaboxContainer);
;// ./js/src/editors/common/components/translations-table/cells/add-or-edit/index.js

const AddOrEditCell = ({
  children
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsx)("td", {
    className: "pll-edit-column pll-column-icon",
    children: children
  });
};
/* harmony default export */ const add_or_edit = (AddOrEditCell);
;// ./js/src/editors/common/components/translations-table/cells/default-language/index.js
/* unused harmony import specifier */ var default_language_DefaultLangIcon;
/* unused harmony import specifier */ var default_language_jsx;
/**
 * Internal dependencies
 */


const DefaultLanguageCell = ({
  isDefault
}) => {
  return /*#__PURE__*/default_language_jsx("td", {
    className: "pll-default-lang-column pll-column-icon",
    children: isDefault && /*#__PURE__*/default_language_jsx(default_language_DefaultLangIcon, {})
  });
};
/* harmony default export */ const default_language = ((/* unused pure expression or super */ null && (DefaultLanguageCell)));
;// ./js/src/editors/common/components/translations-table/cells/delete/index.js
/* unused harmony import specifier */ var delete_jsx;

const DeleteCell = ({
  children
}) => {
  return /*#__PURE__*/delete_jsx("td", {
    className: "pll-delete-column pll-column-icon",
    children: children
  });
};
/* harmony default export */ const cells_delete = ((/* unused pure expression or super */ null && (DeleteCell)));
;// ./js/src/editors/common/components/translations-table/cells/flag/index.js
/**
 * External dependencies.
 */


const FlagCell = ({
  language
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsx)("th", {
    className: "pll-language-column",
    children: !(0,external_lodash_.isEmpty)(language.flag) ? /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      className: "pll-select-flag flag",
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("img", {
        src: language.flag_url,
        alt: language.name,
        title: language.name
      })
    }) : /*#__PURE__*/(0,jsx_runtime.jsxs)("abbr", {
      children: [language.slug, /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
        className: "screen-reader-text",
        children: language.name
      })]
    })
  });
};
/* harmony default export */ const flag = (FlagCell);
;// ./js/src/editors/common/components/translations-table/cells/translation-input/index.js

const TranslationInputCell = ({
  children
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsx)("td", {
    className: "pll-translation-column",
    children: children
  });
};
/* harmony default export */ const translation_input = (TranslationInputCell);
;// ./js/src/editors/common/components/translations-table/cells/index.js
/**
 * Cells components for translations table.
 */






;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/plus.js
/**
 * Plus icon - plus Dashicon.
 */

/**
 * WordPress dependencies
 */


const plus_isPrimitivesComponents = 'undefined' !== typeof wp.primitive;
const plus = plus_isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  width: "20",
  height: "20",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 20 20",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M17 7v3h-5v5h-3v-5h-5v-3h5v-5h3v5h5z"
  })
}) : 'plus';
/* harmony default export */ const icons_plus = (plus);
;// ./js/src/editors/common/components/buttons/add/index.js
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



/**
 * Renders a button to add new translation.
 *
 * @param {Object}   props                Component props.
 * @param {Object}   props.language       Language of the new translation.
 * @param {string}   props.href           URL to add a new translation, pass '#' if managed in REST.
 * @param {boolean}  props.disabled       Whether the button is disabled.
 * @param {Function} props.handleAddClick Callback to add a translation, default to null. Useful only if the button is not a link.
 * @return {React.ReactElement} Button component.
 */

const AddButton = ({
  language,
  href,
  disabled,
  handleAddClick = null
}) => {
  const accessibilityText = (0,external_this_wp_i18n_.sprintf)(
  // translators: %s is a native language name.
  (0,external_this_wp_i18n_.__)('Add a translation in %s', 'polylang-pro'), language.name);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
    href: href,
    disabled: disabled,
    icon: icons_plus,
    label: accessibilityText,
    className: `pll-button`,
    onClick: handleAddClick,
    "data-target-language": language.slug // Store the target language to retrieve it through the click event.
    ,
    children: /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      className: "screen-reader-text",
      children: accessibilityText
    })
  });
};
/* harmony default export */ const add = (AddButton);
;// ./js/src/editors/common/components/buttons/delete/index.js
/* unused harmony import specifier */ var trash;
/* unused harmony import specifier */ var Button;
/* unused harmony import specifier */ var sprintf;
/* unused harmony import specifier */ var delete_;
/* unused harmony import specifier */ var buttons_delete_jsx;
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



/**
 * Renders a button to delete a translation.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.language Language of the existing translation.
 * @param {boolean}  props.disabled Whether the button is disabled.
 * @param {Function} props.onClick  Callback to delete a translation.
 * @return {React.ReactElement} Button component.
 */

const DeleteButton = ({
  language,
  disabled,
  onClick
}) => {
  const translationScreenReaderText = sprintf(
  // translators: %s is a native language name.
  delete_('Delete the translation in %s', 'polylang-pro'), language.name);
  return /*#__PURE__*/buttons_delete_jsx(Button, {
    icon: trash,
    label: translationScreenReaderText,
    disabled: disabled,
    className: "pll-button",
    onClick: onClick,
    children: /*#__PURE__*/buttons_delete_jsx("span", {
      className: "screen-reader-text",
      children: translationScreenReaderText
    })
  });
};
/* harmony default export */ const buttons_delete = ((/* unused pure expression or super */ null && (DeleteButton)));
;// ./js/src/editors/common/components/buttons/persisting-user-data/index.js
/**
 * WordPress dependencies
 */







/**
 * Prepares user preference data for API call.
 *
 * @param {Object}  currentUser        The current user object.
 * @param {string}  userPreferenceName The user preference name.
 * @param {string}  postType           The post type.
 * @param {boolean} isActive           Current active state.
 * @return {Object} Prepared data for API call.
 */

const prepareUserPreferenceData = (currentUser, userPreferenceName, postType, isActive) => {
  /*
   * If the user meta is an empty array, it has never been created.
   * So we convert it as an object to be able to update correctly its value in DB.
   */
  if (undefined === currentUser[userPreferenceName] || Array.isArray(currentUser[userPreferenceName]) && currentUser[userPreferenceName].length === 0) {
    currentUser[userPreferenceName] = {};
  }

  // Updates currentUser preference.
  currentUser[userPreferenceName][postType] = !isActive;
  return {
    [userPreferenceName]: currentUser[userPreferenceName]
  };
};

/**
 * Button to persist user data per post type.
 *
 * @param {Object} props                    The component props.
 * @param {string} props.id                 The button id attribute.
 * @param {string} props.postType           The post type.
 * @param {string} props.userPreferenceName The user preference name.
 * @param {string} props.activeLabel        The active label.
 * @param {string} props.inactiveLabel      The inactive label.
 * @param {Object} props.icon               The icon.
 *
 * @return {React.ReactNode} The Persisting User Data Button.
 */
const PersistingUserDataButton = ({
  id,
  postType,
  userPreferenceName,
  activeLabel,
  inactiveLabel,
  icon
}) => {
  const currentUser = (0,external_this_wp_data_.useSelect)(select => select(external_this_wp_coreData_.store).getCurrentUser(), []);
  const [isLoading, setIsLoading] = (0,external_this_wp_element_.useState)(false);
  const {
    createErrorNotice
  } = (0,external_this_wp_data_.useDispatch)(external_this_wp_notices_.store);
  const buttonInitialState = () => {
    if (undefined === currentUser || undefined === currentUser[userPreferenceName] || undefined === currentUser[userPreferenceName][postType]) {
      return false;
    }
    return currentUser[userPreferenceName][postType];
  };
  const [isActive, setIsActive] = (0,external_this_wp_element_.useState)(buttonInitialState);
  const saveStateInUserPreferences = () => {
    setIsLoading(true);
    const data = prepareUserPreferenceData(currentUser, userPreferenceName, postType, isActive);

    // Saves the preference.
    external_this_wp_apiFetch_default()({
      path: '/wp/v2/users/me',
      data,
      method: 'POST'
    }).then(() => {
      // Update component state only on successful API call.
      setIsActive(prevIsActive => !prevIsActive);
    }).catch(error => {
      createErrorNotice(error.message, {
        type: 'snackbar'
      });
    }).finally(() => {
      // Always reset loading state.
      setIsLoading(false);
    });
  };
  return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
    id: id,
    className: `pll-button pll-before-post-translations-button ${isActive && `wp-ui-text-highlight`}`,
    onClick: saveStateInUserPreferences,
    icon: icon,
    label: isActive ? activeLabel : inactiveLabel,
    isBusy: isLoading,
    disabled: isLoading,
    children: /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      className: "screen-reader-text",
      children: isActive ? activeLabel : inactiveLabel
    })
  });
};
/* harmony default export */ const persisting_user_data = (PersistingUserDataButton);
;// ./js/src/editors/common/components/buttons/machine-translation/index.js
/**
 * WordPress dependencies
 */




/**
 * Internal dependencies
 */



/**
 * Machine Translation Button
 *
 * @return {React.ReactNode|null} The Machine Translation Button, `null` if disabled.
 */

const MachineTranslationButton = () => {
  const postType = (0,external_this_wp_data_.useSelect)(select => select(MODULE_CORE_EDITOR_KEY).getCurrentPostType(), []);
  const machineTranslation = pll_block_editor_plugin_settings?.machine_translation;
  if (!machineTranslation || !machineTranslation.isActive) {
    return null;
  }
  const {
    path_d,
    ...iconProps
  } = machineTranslation.icon; // eslint-disable-line camelcase

  const icon = {
    type: 'svg',
    props: {
      ...iconProps,
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
        d: path_d
      }) // eslint-disable-line camelcase
    }
  };
  return /*#__PURE__*/(0,jsx_runtime.jsx)(persisting_user_data, {
    id: 'pll-machine-translation',
    postType,
    userPreferenceName: `pll_machine_translation_${machineTranslation.slug}`,
    activeLabel: (0,external_this_wp_i18n_.sprintf)(/* translators: %s is the name of the machine translation service. */
    (0,external_this_wp_i18n_.__)('Deactivate %s machine translation', 'polylang-pro'), machineTranslation.name),
    inactiveLabel: (0,external_this_wp_i18n_.sprintf)(/* translators: %s is the name of the machine translation service. */
    (0,external_this_wp_i18n_.__)('Activate %s machine translation', 'polylang-pro'), machineTranslation.name),
    icon
  });
};
/* harmony default export */ const machine_translation = (MachineTranslationButton);
;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/duplication.js
/**
 * Duplication icon - admin-page Dashicon.
 */

/**
 * WordPress dependencies
 */


const duplication_isPrimitivesComponents = 'undefined' !== typeof wp.primitives;
const duplication = duplication_isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  width: "20",
  height: "20",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 20 20",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M6 15v-13h10v13h-10zM5 16h8v2h-10v-13h2v11z"
  })
}) : 'admin-page';
/* harmony default export */ const icons_duplication = (duplication);
;// ./js/src/editors/common/components/buttons/duplicate/index.js
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



/**
 * Internal dependencies
 */



/**
 * Duplicate Button
 *
 * @param {Object} props The component props.
 *
 * @return {React.ReactNode|null} The Duplicate Button, `null` if loading.
 */

const DuplicateButton = props => {
  const postType = (0,external_this_wp_data_.useSelect)(select => select(MODULE_CORE_EDITOR_KEY).getCurrentPostType(), []);
  if (!postType) {
    return null;
  }
  const newProps = {
    ...props,
    id: 'pll-duplicate',
    userPreferenceName: 'pll_duplicate_content',
    /* translators: accessibility text */
    activeLabel: (0,external_this_wp_i18n_.__)('Deactivate the content duplication', 'polylang-pro'),
    /* translators: accessibility text */
    inactiveLabel: (0,external_this_wp_i18n_.__)('Activate the content duplication', 'polylang-pro'),
    icon: icons_duplication,
    postType
  };
  return /*#__PURE__*/(0,jsx_runtime.jsx)(persisting_user_data, {
    ...newProps
  });
};
/* harmony default export */ const duplicate = (DuplicateButton);
;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/pencil.js
/**
 * Pencil icon - edit Dashicon.
 */

/**
 * WordPress dependencies
 */


const pencil_isPrimitivesComponents = 'undefined' !== typeof wp.primitives;
const pencil = pencil_isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  width: "20",
  height: "20",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 20 20",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M13.89 3.39l2.71 2.72c0.46 0.46 0.42 1.24 0.030 1.64l-8.010 8.020-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.030c0.39-0.39 1.22-0.39 1.68 0.070zM11.16 6.18l-5.59 5.61 1.11 1.11 5.54-5.65zM8.19 14.41l5.58-5.6-1.070-1.080-5.59 5.6z"
  })
}) : 'edit';
/* harmony default export */ const icons_pencil = (pencil);
;// ./js/src/editors/common/components/buttons/edit/index.js
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



/**
 * Renders a button to edit existing translation.
 *
 * @param {Object}   props                 Component props.
 * @param {Object}   props.language        Language of the existing translation.
 * @param {string}   props.href            URL to edit a new translation, pass '#' if managed in REST.
 * @param {boolean}  props.disabled        Whether the button is disabled.
 * @param {Function} props.handleEditClick Callback to edit a translation, default to null. Useful only if the button is not a link.
 * @return {React.ReactElement} Button component.
 */

const EditButton = ({
  language,
  href,
  disabled,
  handleEditClick = null
}) => {
  const accessibilityText = (0,external_this_wp_i18n_.sprintf)(/* translators: accessibility text, %s is a native language name. For example Deutsch for German or Français for french. */
  (0,external_this_wp_i18n_.__)('Edit the translation in %s', 'polylang-pro'), language.name);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
    href: href,
    disabled: disabled,
    icon: icons_pencil,
    label: accessibilityText,
    className: `pll-button`,
    onClick: handleEditClick,
    "data-target-language": language.slug // Store the target language to retrieve it through the click event.
    ,
    children: /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      className: "screen-reader-text",
      children: accessibilityText
    })
  });
};
/* harmony default export */ const edit = (EditButton);
;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/synchronization.js
/**
 * Synchronization icon - controls-repeat Dashicon.
 */

/**
 * WordPress dependencies
 */


const synchronization_isPrimitivesComponents = 'undefined' !== typeof wp.primitives;
const synchronization = synchronization_isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  width: "20",
  height: "20",
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 20 20",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M5 7v3l-2 1.5v-6.5h11v-2l4 3.010-4 2.99v-2h-9zM15 13v-3l2-1.5v6.5h-11v2l-4-3.010 4-2.99v2h9z"
  })
}) : 'controls-repeat';
/* harmony default export */ const icons_synchronization = (synchronization);
// EXTERNAL MODULE: external {"this":["wp","editor"]}
var external_this_wp_editor_ = __webpack_require__(2);
;// ./js/src/editors/common/components/buttons/synchronization/use-dispatch-current-post-title.js
/**
 * WordPress dependencies
 */


/**
 * Custom hook to dispatch the current post title to the translation table reducer.
 *
 * @param {Object}      language                The language object.
 * @param {Function}    translationTableReducer The translation table reducer.
 * @param {Object}      sourcePost              The source post object.
 * @param {Object|null} post                    The post object, null when no translation.
 * @return {void}
 */
const useDispatchCurrentPostTitle = (language, translationTableReducer, sourcePost, post) => {
  (0,external_this_wp_element_.useEffect)(() => {
    if (sourcePost.pll_sync_post[language.slug] && sourcePost.translations[language.slug] && sourcePost.title !== post?.title) {
      const translatedPost = {
        ...sourcePost,
        // Keep synchronized post title up to date.
        title: sourcePost.title
      };
      translatedPost.id = post?.id ?? 0;
      translationTableReducer({
        type: 'add_translation',
        lang: language,
        post: {
          ...translatedPost
        }
      });
    }
  }, [language, translationTableReducer, sourcePost, post]);
};
/* harmony default export */ const use_dispatch_current_post_title = (useDispatchCurrentPostTitle);
;// ./js/src/editors/common/components/buttons/synchronization/index.js
/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */







/**
 * Internal dependencies
 */



/**
 * Prepares the sync posts object to be stored with a REST field.
 *
 * @param {Object} syncPosts The sync posts object.
 * @return {Object} The prepared sync posts object, with boolean values converted to strings.
 */

const prepareSyncPosts = syncPosts => {
  return Object.fromEntries(Object.entries(syncPosts).map(([key, value]) => [key, value.toString()]));
};

/**
 * Synchronization button component.
 *
 * @param {Object}  props                         The props object.
 * @param {Object}  props.post                    The post object.
 * @param {Object}  props.language                The language object.
 * @param {Object}  props.translationTableReducer The translation table reducer.
 * @param {boolean} props.disabled                Whether the button is disabled.
 * @return {React.Component} The synchronization button component.
 */
const SynchronizationButton = ({
  post,
  language,
  translationTableReducer,
  disabled
}) => {
  const [isSynchronized, setIsSynchronized] = (0,external_this_wp_element_.useState)(false);
  const [isTranslated, setIsTranslated] = (0,external_this_wp_element_.useState)(false);
  const [isModalOpen, setIsModalOpen] = (0,external_this_wp_element_.useState)(false);
  const sourcePost = (0,external_this_wp_data_.useSelect)(() => {
    return (0,external_this_wp_data_.select)(external_this_wp_editor_.store).getCurrentPost();
  }, []);
  use_dispatch_current_post_title(language, translationTableReducer, sourcePost, post);
  const buttonLabel = (0,external_this_wp_element_.useMemo)(() => {
    return isSynchronized ? (0,external_this_wp_i18n_.__)('Unsynchronize this post', 'polylang-pro') : (0,external_this_wp_i18n_.__)('Synchronize this post', 'polylang-pro');
  }, [isSynchronized]);
  const toggleSynchronizationStatus = () => {
    const syncPosts = sourcePost.pll_sync_post;
    if (isSynchronized) {
      delete syncPosts[language.slug];
      setIsSynchronized(false);
    } else {
      syncPosts[language.slug] = true;
      setIsSynchronized(true);
      setIsTranslated(true);
      if (!post) {
        post = {
          id: 0
        };
      }
      translationTableReducer({
        type: 'add_translation',
        lang: language,
        post: {
          // Optimistic rendering.
          ...post,
          title: sourcePost.title
        }
      });
      (0,external_this_wp_data_.dispatch)(external_this_wp_coreData_.store).invalidateResolution('getEntityRecord', ['postType', post.type, post.id]);
    }
    (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).editPost({
      pll_sync_post: prepareSyncPosts(syncPosts)
    });
  };
  (0,external_this_wp_element_.useEffect)(() => {
    setIsTranslated(post?.id !== undefined);
    setIsSynchronized(sourcePost.pll_sync_post[language.slug] !== undefined);
  }, [sourcePost, language, post]);
  return /*#__PURE__*/(0,jsx_runtime.jsxs)(jsx_runtime.Fragment, {
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
      icon: icons_synchronization,
      label: buttonLabel,
      id: `pll_sync_post[${language.slug}]`,
      className: `pll-button ${isSynchronized && 'wp-ui-text-highlight'}`,
      onClick: () => {
        if (!isSynchronized && isTranslated) {
          setIsModalOpen(true);
        } else {
          toggleSynchronizationStatus();
        }
      },
      disabled: disabled,
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
        className: "screen-reader-text",
        children: buttonLabel
      })
    }), isModalOpen && /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_components_.Modal, {
      title: (0,external_this_wp_i18n_.__)('Synchronize this post', 'polylang-pro'),
      onRequestClose: () => {
        setIsModalOpen(false);
      },
      children: [/*#__PURE__*/(0,jsx_runtime.jsx)("p", {
        children: (0,external_this_wp_i18n_.__)('You are about to overwrite an existing translation. Are you sure you want to proceed?', 'polylang-pro')
      }), /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
        role: "group",
        className: "components-button-group buttons",
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
          variant: "tertiary",
          onClick: () => {
            setIsModalOpen(false);
          },
          type: "button",
          children: (0,external_this_wp_i18n_.__)('Cancel', 'polylang-pro')
        }), /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
          children: "\xA0"
        }), /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
          variant: "primary",
          onClick: () => {
            toggleSynchronizationStatus();
            setIsModalOpen(false);
          },
          type: "submit",
          children: (0,external_this_wp_i18n_.__)('Synchronize', 'polylang-pro')
        })]
      })]
    })]
  });
};
/* harmony default export */ const buttons_synchronization = (SynchronizationButton);
;// ./js/src/editors/common/components/buttons/index.js
/**
 * Buttons components.
 */







// EXTERNAL MODULE: external {"this":["wp","htmlEntities"]}
var external_this_wp_htmlEntities_ = __webpack_require__(989);
;// ./js/src/editors/common/components/translations-table/input/index.js
/**
 * WordPress dependencies
 */








/**
 * Internal dependencies
 */


/**
 * Translation input component.
 *
 * @param {Object} props                   The component props.
 * @param {Object} props.language          The language object.
 * @param {Object} props.source            The source post object.
 * @param {Object} props.translation       The translation post object.
 * @param {Object} props.tableDispatch     The table dispatch function.
 * @param {Map}    props.translationsTable The translations table object.
 * @return {React.Component} The translation input component.
 */

const TranslationInput = ({
  language,
  source,
  translation,
  tableDispatch,
  translationsTable
}) => {
  const [options, setOptions] = (0,external_this_wp_element_.useState)(translation ? [{
    value: translation.id,
    label: getPostTitle(translation)
  }] : []);
  const [value, setValue] = (0,external_this_wp_element_.useState)(translation ? translation.id : null);
  const [isLoading, setIsLoading] = (0,external_this_wp_element_.useState)(false);
  (0,external_this_wp_element_.useEffect)(() => {
    if (translation && options.length === 0) {
      setOptions([{
        value: translation.id,
        label: getPostTitle(translation)
      }]);
    }
  }, [translation, options]);
  (0,external_this_wp_element_.useEffect)(() => {
    if (translation) {
      if (options.find(option => option.value === translation.id && option.label !== getPostTitle(translation) || option.value !== translation.id && 0 === option.value && option.label === getPostTitle(translation))) {
        // Set options for optimistically rendered translation.
        setOptions([{
          value: translation.id,
          label: getPostTitle(translation)
        }]);
      }
      setValue(translation.id);
    } else {
      setValue(null);
    }
  }, [translation, options]); // eslint-disable-line react-hooks/exhaustive-deps

  const onInputChange = nextValue => {
    setValue(nextValue);
    if ((0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY).isEditedPostNew()) {
      (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).savePost();
    }
    if (!nextValue) {
      tableDispatch({
        type: 'remove_translation',
        lang: language
      });
      const newTranslations = {
        ...transformTranslationsTableToObject(translationsTable)
      };
      delete newTranslations[language.slug];
      (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).editPost({
        translations: newTranslations
      });
      return;
    }
    const postToRender = getPostToRender(options, nextValue);
    if (!postToRender) {
      return;
    }
    tableDispatch({
      type: 'add_translation',
      lang: language,
      post: postToRender // Optimistically rendered post.
    });
    (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).editPost({
      translations: {
        ...transformTranslationsTableToObject(translationsTable),
        [language.slug]: parseInt(nextValue, 10)
      }
    });
  };
  const fetchUntranslatedPosts = (0,external_this_wp_element_.useCallback)(search => {
    if ('' === search) {
      return [];
    }
    setIsLoading(true);
    external_this_wp_apiFetch_default()({
      path: (0,external_this_wp_url_.addQueryArgs)('/pll/v1/untranslated-posts', {
        search,
        include: source.id,
        untranslated_in: source.lang,
        lang: language.slug,
        type: source.type,
        context: 'edit'
      })
    }).then(posts => {
      setOptions(posts.map(post => {
        return {
          value: parseInt(post.id, 10),
          label: getPostTitle(post)
        };
      }));
    }).finally(() => {
      setIsLoading(false);
    });
  }, [source, language, setOptions]);
  const debounce = (0,external_this_wp_element_.useCallback)(callback => {
    let timeoutId;
    return (...args) => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => callback(...args), 300);
    };
  }, []);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ComboboxControl, {
    value: value,
    options: options,
    isLoading: isLoading,
    onChange: onInputChange,
    placeholder: (0,external_this_wp_i18n_.__)('Search for a post', 'polylang-pro'),
    onFilterValueChange: debounce(fetchUntranslatedPosts),
    __next40pxDefaultSize: true
  });
};

/**
 * Gets the post title.
 *
 * @param {Object} post The post object.
 * @return {string} The post title, or an empty string if the post has no title.
 */
const getPostTitle = post => {
  /**
   * @member {{rendered: ?string, raw: ?string}|string} post.title
   */
  return (0,external_this_wp_htmlEntities_.decodeEntities)(post.title?.rendered ?? post.title?.raw ?? post.title ?? '');
};

/**
 * Gets the post to render.
 *
 * @param {Object} options   The options object.
 * @param {string} nextValue The next value.
 * @return {Object|null} The post to render, or null if the post is not found.
 */
const getPostToRender = (options, nextValue) => {
  const value = options.find(option => option.value === nextValue);
  if (!value) {
    return null;
  }
  return {
    id: value.value,
    title: value.label
  };
};

/**
 * Transforms the translations table to an object.
 *
 * @param {Map<Object, Object>} translationsTable The translations table.
 * @return {Object<string, number>} The translations object.
 */
const transformTranslationsTableToObject = translationsTable => {
  const translationsObject = {};
  translationsTable.forEach((translationPost, lang) => {
    if (!translationPost || !translationPost.id) {
      return;
    }
    translationsObject[lang.slug] = translationPost.id;
  });
  return translationsObject;
};
/* harmony default export */ const input = (TranslationInput);
;// ./js/src/editors/common/components/translations-table/rows/index.js
/**
 * Internal dependencies.
 */


const TranslationRow = ({
  language,
  children
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsxs)(jsx_runtime.Fragment, {
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(flag, {
      language: language
    }), children]
  });
};
/* harmony default export */ const rows = (TranslationRow);
;// ./js/src/editors/common/components/plugin-feature-flag-button/index.js
/**
 * Defines as extensibility slot for the metabox .
 */

/**
 * WordPress dependencies
 */


const {
  Fill,
  Slot
} = (0,external_this_wp_components_.createSlotFill)('PluginFeatureFlagButton');

/**
 * Plugin feature flag button.
 *
 * @example
 * ```js
 * import { registerPlugin } from '@wordpress/plugins';
 * import { PluginFeatureFlagButton } from '@wpsyntex/polylang';
 * import { YourCustomButton } from './your-custom-button';
 *
 * registerPlugin( 'pll-plugin-feature-flag-button', {
 * 	render: () => (
 * 		<PluginFeatureFlagButton>
 * 			<YourCustomButton />
 * 		</PluginFeatureFlagButton>
 * 	),
 * } );
 * ```
 *
 * @param {Object}          props           The component props.
 * @param {React.ReactNode} props.children  The children.
 * @param {string}          props.className The class name.
 *
 * @return {React.ReactNode} The Plugin Feature Flag Button.
 */
const PluginFeatureFlagButton = ({
  children,
  className
}) => /*#__PURE__*/(0,jsx_runtime.jsx)(Fill, {
  children: /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
    className: className,
    children: children
  })
});
PluginFeatureFlagButton.Slot = Slot;
/* harmony default export */ const plugin_feature_flag_button = (PluginFeatureFlagButton);
;// ./js/src/editors/common/components/translations-table/wrapper/index.js
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


const TranslationsTableWrapper = ({
  children
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
    id: "post-translations",
    className: "translations",
    children: [/*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
      className: "pll-translations-table-header",
      children: [/*#__PURE__*/(0,jsx_runtime.jsx)("p", {
        className: "pll-translations-table-header-title",
        children: /*#__PURE__*/(0,jsx_runtime.jsx)("strong", {
          children: (0,external_this_wp_i18n_.__)('Translations', 'polylang-pro')
        })
      }), /*#__PURE__*/(0,jsx_runtime.jsx)(plugin_feature_flag_button.Slot, {
        children: fills => fills.length > 0 && /*#__PURE__*/(0,jsx_runtime.jsx)(jsx_runtime.Fragment, {
          children: /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
            className: "pll-plugin-feature-button-container",
            children: fills
          })
        })
      })]
    }), /*#__PURE__*/(0,jsx_runtime.jsx)("table", {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("tbody", {
        children: children
      })
    })]
  });
};
/* harmony default export */ const translations_table_wrapper = (TranslationsTableWrapper);
;// ./js/src/editors/common/components/translations-table/plugin-feature-table-row-button/index.js
/**
 * Defines as extensibility slot for the translations table row.
 */

/**
 * WordPress dependencies
 */



const createSlotFillWithId = id => (0,external_this_wp_components_.createSlotFill)(`PluginFeatureTableRowButton_${id}`);

/**
 * Plugin feature table row button fill.
 * Ideally one per language.
 *
 * @example
 * ```js
 * import { registerPlugin } from '@wordpress/plugins';
 * import { PluginFeatureTableRowButtonFill } from '@wpsyntex/polylang';
 * import { YourCustomButton } from './your-custom-button';
 *
 * // You may want to loop through the languages and register one fill per language.
 * registerPlugin( 'pll-plugin-feature-table-row-button-fill', {
 * 	render: () => (
 * 		<PluginFeatureTableRowButtonFill languageSlug="en">
 * 			<YourCustomButton />
 * 		</PluginFeatureTableRowButtonFill>
 * 	),
 * } );
 * ```
 *
 * @param {Object}          props              The component props.
 * @param {React.ReactNode} props.children     The children.
 * @param {string}          props.className    The class name.
 * @param {string}          props.languageSlug The language slug, used to generate a unique slot fill.
 *
 * @return {React.ReactNode} The plugin feature table row button fill.
 */
const PluginFeatureTableRowButtonFill = ({
  children,
  className,
  languageSlug
}) => {
  const {
    Fill
  } = createSlotFillWithId(languageSlug);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(Fill, {
    children: drilledProps => {
      return /*#__PURE__*/(0,jsx_runtime.jsx)("div", {
        className: className,
        children: external_this_wp_element_.Children.map(children, child => (0,external_this_wp_element_.cloneElement)(child, {
          language: drilledProps.language,
          translation: drilledProps.translation,
          translationTableReducer: drilledProps.translationTableReducer
        }))
      });
    }
  });
};

/**
 * Plugin feature table row button slot.
 * Ideally one per language.
 *
 * @param {Object} props                         The component props.
 * @param {string} props.language                The language object, used to generate a unique slot fill.
 * @param {Object} props.translation             The translated object.
 * @param {Object} props.translationTableReducer The table dispatch object.
 *
 * @return {React.ReactNode} The plugin feature table row button slot.
 */
const PluginFeatureTableRowButtonSlot = ({
  language,
  translation,
  translationTableReducer
}) => {
  const {
    Slot
  } = createSlotFillWithId(language.slug);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(Slot, {
    bubblesVirtually: true,
    as: "td",
    fillProps: {
      language,
      translation,
      translationTableReducer
    },
    className: "pll-feature-button-row-container"
  });
};

;// ./js/src/editors/common/hooks/use-authorized-languages.js
/**
 * WordPress dependencies
 */



/**
 * Internal dependencies
 */


/**
 * Custom hook to get the memoized authorized languages.
 *
 * @return {Map} The memoized authorized languages.
 */
const useAuthorizedLanguages = () => {
  /**
   * @type {Map<string, Object>}
   */
  const languages = (0,external_this_wp_data_.useSelect)(select => select(MODULE_KEY).getLanguages());
  const authorizedLanguagesSlugs = pll_block_editor_plugin_settings?.authorizedLanguagesSlugs;
  return (0,external_this_wp_element_.useMemo)(() => {
    if (!authorizedLanguagesSlugs) {
      return languages;
    }
    return new Map(authorizedLanguagesSlugs.map(languageSlug => {
      const language = languages.get(languageSlug);
      if (language) {
        return [languageSlug, language];
      }
      return null;
    }).filter(Boolean));
  }, [languages, authorizedLanguagesSlugs]);
};
/* harmony default export */ const use_authorized_languages = (useAuthorizedLanguages);
;// ./js/src/editors/common/components/translations-table/post-editor/index.js
/**
 * WordPress dependencies
 */





/**
 * Internal dependencies
 */








/**
 * Add or edit button component, used in post editor because of clickable links.
 *
 * @param {Object}  props             The props object.
 * @param {boolean} props.canUpdate   Whether the user can update the translation.
 * @param {boolean} props.canCreate   Whether the user can create the translation.
 * @param {boolean} props.canRead     Whether the user can read the translation.
 * @param {Object}  props.language    The language object.
 * @param {Object}  props.currentPost The current post object.
 * @return {React.ReactNode} The add or edit button component.
 */

const AddOrEditButton = ({
  canUpdate,
  canCreate,
  canRead,
  language,
  currentPost
}) => {
  const [translationId, setTranslationId] = (0,external_this_wp_element_.useState)(currentPost.translations[language.slug] ? currentPost.translations[language.slug] : null);
  (0,external_this_wp_element_.useEffect)(() => {
    setTranslationId(currentPost.translations[language.slug] ? currentPost.translations[language.slug] : null);
  }, [currentPost, language, setTranslationId]);
  if (canUpdate || canRead) {
    // Show Edit button for existing translations (disabled if user can only read).
    // eslint-disable-next-line prettier/prettier
    const baseEditUrl = `${location.origin}/${(0,external_this_wp_url_.getPath)(location.href)}`.replace('post-new.php', 'post.php');
    const editUrl = `${baseEditUrl}?post=${translationId}&action=edit`;
    return /*#__PURE__*/(0,jsx_runtime.jsx)(edit, {
      href: editUrl,
      language: language,
      disabled: !canUpdate
    });
  }
  return /*#__PURE__*/(0,jsx_runtime.jsx)(add, {
    href: pll_block_editor_plugin_settings.new_post_translation_links[language.slug],
    language: language,
    disabled: !canCreate
  });
};

/**
 * Post Editor Translations Table component.
 *
 * @param {Object}   props                   The component props.
 * @param {Object}   props.currentPost       The current post.
 * @param {Map}      props.translationsTable The translations table, contains language object as key and post object as value.
 * @param {Function} props.tableDispatch     The translations table dispatch function.
 * @return {React.Component} The Post Editor Translations Table component.
 */
const PostEditorTranslationsTable = ({
  currentPost,
  translationsTable,
  tableDispatch
}) => {
  const table = [];
  const authorizedLanguages = use_authorized_languages();
  translationsTable.forEach((translation, language) => {
    // Don't display current post in the translation table.
    if (currentPost.lang === language.slug) {
      return null;
    }
    const canCreate = !translation && authorizedLanguages.has(language.slug) && (0,external_this_wp_data_.select)(external_this_wp_coreData_.store).canUser('create', {
      kind: 'postType',
      name: currentPost.type,
      lang: language.slug
    });
    const canUpdate = translation && (0,external_this_wp_data_.select)(external_this_wp_coreData_.store).canUser('update', {
      kind: 'postType',
      name: currentPost.type,
      id: translation.id
    });
    table.push(/*#__PURE__*/(0,jsx_runtime.jsx)("tr", {
      children: /*#__PURE__*/(0,jsx_runtime.jsxs)(rows, {
        language: language,
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(add_or_edit, {
          children: /*#__PURE__*/(0,jsx_runtime.jsx)(AddOrEditButton, {
            canUpdate: canUpdate,
            canCreate: canCreate,
            canRead: !!translation,
            language: language,
            currentPost: currentPost
          })
        }), /*#__PURE__*/(0,jsx_runtime.jsx)(PluginFeatureTableRowButtonSlot, {
          language: language,
          translation: translation,
          translationTableReducer: tableDispatch
        }), /*#__PURE__*/(0,jsx_runtime.jsx)(translation_input, {
          children: /*#__PURE__*/(0,jsx_runtime.jsx)(input, {
            language: language,
            source: currentPost,
            translation: translation,
            tableDispatch: tableDispatch,
            translationsTable: translationsTable
          })
        })]
      })
    }, language.slug));
  });
  return /*#__PURE__*/(0,jsx_runtime.jsx)(translations_table_wrapper, {
    children: table
  });
};
/* harmony default export */ const post_editor = (PostEditorTranslationsTable);
;// ./js/src/editors/common/components/delete-modal-body/index.js
/* unused harmony import specifier */ var delete_modal_body_;
/* unused harmony import specifier */ var delete_modal_body_jsxs;
/* unused harmony import specifier */ var delete_modal_body_jsx;
/* unused harmony import specifier */ var delete_modal_body_Fragment;
/**
 * WordPress dependencies
 */


const DeleteModalBody = ({
  isDefaultLang
}) => {
  const defaultLangText = () => {
    if (!isDefaultLang) {
      return null;
    }
    return /*#__PURE__*/delete_modal_body_jsxs("p", {
      children: [delete_modal_body_('You are about to delete an entity in the default language.', 'polylang-pro'), /*#__PURE__*/delete_modal_body_jsx("br", {}), delete_modal_body_('This will delete its customizations and all its corresponding translations.', 'polylang-pro')]
    });
  };
  return /*#__PURE__*/delete_modal_body_jsxs(delete_modal_body_Fragment, {
    children: [defaultLangText(), /*#__PURE__*/delete_modal_body_jsx("p", {
      children: delete_modal_body_('Are you sure you want to delete this translation?', 'polylang-pro')
    })]
  });
};
/* harmony default export */ const delete_modal_body = ((/* unused pure expression or super */ null && (DeleteModalBody)));
;// ./js/src/editors/common/components/delete-with-confirmation/use-delete-post.js
/* unused harmony import specifier */ var coreStore;
/* unused harmony import specifier */ var use_delete_post_;
/* unused harmony import specifier */ var use_delete_post_sprintf;
/* unused harmony import specifier */ var useDispatch;
/* unused harmony import specifier */ var noticesStore;
/* unused harmony import specifier */ var use_delete_post_POST_TYPE_WITH_TRASH;
/**
 * WordPress dependencies
 *
 */





const useDeletePost = () => {
  const {
    deleteEntityRecord
  } = useDispatch(coreStore);
  const {
    createSuccessNotice,
    createErrorNotice
  } = useDispatch(noticesStore);
  const handleDelete = async post => {
    try {
      const forceDelete = !use_delete_post_POST_TYPE_WITH_TRASH.includes(post.type);
      await deleteEntityRecord('postType', post.type, post.id, {
        force: forceDelete
      }, {
        throwOnError: true
      });
      createSuccessNotice(use_delete_post_('The translation has been deleted.', 'polylang-pro'), {
        type: 'snackbar'
      });
    } catch (error) {
      createErrorNotice(use_delete_post_sprintf(/* translators: %s: Error message describing why the post could not be deleted. */
      use_delete_post_('Unable to delete the translation. %s', 'polylang-pro'), error?.message), {
        type: 'snackbar'
      });
    }
  };
  return {
    handleDelete
  };
};
/* harmony default export */ const use_delete_post = ((/* unused pure expression or super */ null && (useDeletePost)));
;// ./js/src/editors/common/components/delete-with-confirmation/maybe-redirect.js
/* unused harmony import specifier */ var addQueryArgs;
/**
 * WordPress dependencies
 *
 */

const maybeRedirect = (postLang, postType) => {
  if (!postLang.is_default || 'page' === postType) {
    return;
  }
  const newUrl = window.location.origin + wp.sanitize.stripTags(window.location.pathname); // phpcs:ignore WordPressVIPMinimum.JS.Window.location

  const queryString = {};
  switch (postType) {
    case 'wp_navigation':
      queryString.path = '/navigation';
      break;
    case 'wp_block':
      queryString.path = '/patterns';
      queryString.categoryType = postType;
      break;
    case 'wp_template_part':
      queryString.path = '/' + postType + '/all';
      break;
  }
  location.href = addQueryArgs(newUrl, queryString); // eslint-disable-line no-undef
};
/* harmony default export */ const maybe_redirect = ((/* unused pure expression or super */ null && (maybeRedirect)));
;// ./js/src/editors/common/components/delete-with-confirmation/index.js
/* unused harmony import specifier */ var Modal;
/* unused harmony import specifier */ var delete_with_confirmation_Button;
/* unused harmony import specifier */ var delete_with_confirmation_;
/* unused harmony import specifier */ var useState;
/* unused harmony import specifier */ var delete_with_confirmation_useSelect;
/* unused harmony import specifier */ var coreDataStore;
/* unused harmony import specifier */ var delete_with_confirmation_DeleteButton;
/* unused harmony import specifier */ var delete_with_confirmation_DeleteModalBody;
/* unused harmony import specifier */ var delete_with_confirmation_useDeletePost;
/* unused harmony import specifier */ var delete_with_confirmation_maybeRedirect;
/* unused harmony import specifier */ var delete_with_confirmation_useAuthorizedLanguages;
/* unused harmony import specifier */ var delete_with_confirmation_jsxs;
/* unused harmony import specifier */ var delete_with_confirmation_Fragment;
/* unused harmony import specifier */ var delete_with_confirmation_jsx;
/**
 * WordPress Dependencies.
 *
 */






/**
 * Internal Dependencies.
 */






/**
 * Delete with confirmation component.
 *
 * @param {Object}   props                 The component props.
 * @param {Object}   props.post            The post object.
 * @param {Object}   props.language        The language object.
 * @param {Function} props.onDeleteSuccess The on delete success function.
 * @return {React.Component} The Delete with confirmation component.
 */

const DeleteWithConfirmation = ({
  post,
  language,
  onDeleteSuccess
}) => {
  const [isOpen, setOpen] = useState(false);
  const openModal = () => setOpen(true);
  const closeModal = () => setOpen(false);
  const authorizedLanguages = delete_with_confirmation_useAuthorizedLanguages();
  const canTrash = delete_with_confirmation_useSelect(select => {
    return post && select(coreDataStore).canUser('delete', {
      kind: 'postType',
      name: post.type,
      id: post.id
    });
  }, [post]);
  const {
    handleDelete
  } = delete_with_confirmation_useDeletePost();
  return /*#__PURE__*/delete_with_confirmation_jsxs(delete_with_confirmation_Fragment, {
    children: [/*#__PURE__*/delete_with_confirmation_jsx(delete_with_confirmation_DeleteButton, {
      onClick: openModal,
      language: language,
      disabled: !canTrash || !authorizedLanguages.has(language.slug)
    }), isOpen && /*#__PURE__*/delete_with_confirmation_jsxs(Modal, {
      title: "Delete",
      onRequestClose: closeModal,
      children: [/*#__PURE__*/delete_with_confirmation_jsx(delete_with_confirmation_DeleteModalBody, {
        isDefaultLang: language.is_default && 'page' !== post?.type // No message for default language deletion with a page.
      }), /*#__PURE__*/delete_with_confirmation_jsxs("div", {
        role: "group",
        className: "components-button-group buttons",
        children: [/*#__PURE__*/delete_with_confirmation_jsx(delete_with_confirmation_Button, {
          variant: "tertiary",
          onClick: closeModal,
          type: "button",
          children: delete_with_confirmation_('Cancel', 'polylang-pro')
        }), /*#__PURE__*/delete_with_confirmation_jsx("span", {
          children: "\xA0"
        }), /*#__PURE__*/delete_with_confirmation_jsx(delete_with_confirmation_Button, {
          variant: "primary",
          onClick: () => {
            handleDelete(post, post.type).then(() => onDeleteSuccess());
            closeModal();
            delete_with_confirmation_maybeRedirect(language, post.type);
          },
          type: "submit",
          children: delete_with_confirmation_('Delete', 'polylang-pro')
        })]
      })]
    })]
  });
};
/* harmony default export */ const delete_with_confirmation = ((/* unused pure expression or super */ null && (DeleteWithConfirmation)));
;// ./js/src/editors/common/hooks/use-default-language.js
/* unused harmony import specifier */ var use_default_language_select;
/* unused harmony import specifier */ var useMemo;
/* unused harmony import specifier */ var use_default_language_;
/* unused harmony import specifier */ var use_default_language_MODULE_KEY;
/**
 * WordPress dependencies
 */




/**
 * Internal dependencies
 */


/**
 * Custom hook to get the default language and memoize it.
 *
 * @return {Object} The default language.
 * @throws {Error} If the default language is not found.
 */
const useDefaultLanguage = () => {
  return useMemo(() => {
    const languages = use_default_language_select(use_default_language_MODULE_KEY).getLanguages();
    const defaultLanguage = Array.from(languages.values()).find(language => language.is_default);
    if (!defaultLanguage) {
      throw new Error(use_default_language_('Default language not found, please check your languages settings.', 'polylang-pro'));
    }
    return defaultLanguage;
  }, []);
};
/* harmony default export */ const use_default_language = ((/* unused pure expression or super */ null && (useDefaultLanguage)));
;// ./js/src/editors/common/components/translations-table/site-editor/use-create-translation.js
/* unused harmony import specifier */ var use_create_translation_;
/* unused harmony import specifier */ var use_create_translation_useDispatch;
/* unused harmony import specifier */ var use_create_translation_coreStore;
/* unused harmony import specifier */ var use_create_translation_noticesStore;
/* unused harmony import specifier */ var apiFetch;
/* unused harmony import specifier */ var useCallback;
/* unused harmony import specifier */ var use_create_translation_useDefaultLanguage;
/**
 * WordPress dependencies
 */







/**
 * Internal dependencies
 */

const useCreateTranslation = () => {
  const {
    createSuccessNotice,
    createErrorNotice
  } = use_create_translation_useDispatch(use_create_translation_noticesStore);
  const {
    saveEntityRecord
  } = use_create_translation_useDispatch(use_create_translation_coreStore);
  const defaultLanguage = use_create_translation_useDefaultLanguage();

  /**
   * Persists the default language template part.
   *
   * @param {Object} templatePart The template part object.
   * @return {Promise<Object>} The persisted template part object.
   */
  const persistDefaultLanguageTemplatePart = useCallback(templatePart => {
    const promise = saveEntityRecord('postType', 'wp_template_part', {
      id: templatePart.id,
      lang: defaultLanguage.slug,
      slug: templatePart.slug
    });
    promise.catch(error => {
      const errorMessage = error.message && error.code !== 'unknown_error' ? error.message : use_create_translation_('An error occurred while persisting the default language template part.', 'polylang-pro');
      createErrorNotice(errorMessage, {
        type: 'snackbar'
      });
    });
    return promise;
  }, [defaultLanguage, saveEntityRecord, createErrorNotice]);

  /**
   * Handles the creation of a post translation using duplication method.
   *
   * @param {Object} language The language object.
   * @param {Object} post     The post object.
   * @return {void}
   */
  const handleCreateTranslation = async (language, post) => {
    try {
      const translation = await apiFetch({
        path: '/pll/v1/translation',
        method: 'POST',
        data: {
          from_post: 'wp_template_part' === post.type || 'wp_template' === post.type ? post.wp_id : post.id,
          lang: language.slug,
          action: 'duplicate'
        }
      });
      createSuccessNotice(use_create_translation_('The translation is created, you will be redirected.', 'polylang-pro'), {
        type: 'snackbar'
      });
      const previousIdInUrl = 'wp_template_part' === post.type || 'wp_template' === post.type ? post.slug : post.id;
      const nextIdInUrl = 'wp_template_part' === translation.type || 'wp_template' === translation.type ? translation.slug : translation.id;
      location.href = location.href.replace(encodeURIComponent(previousIdInUrl), encodeURIComponent(nextIdInUrl));
    } catch (error) {
      const errorMessage = error.message && error.code !== 'unknown_error' ? error.message : use_create_translation_('An error occurred while creating the translation.', 'polylang-pro');
      createErrorNotice(errorMessage, {
        type: 'snackbar'
      });
    }
  };
  return {
    handleCreateTranslation,
    persistDefaultLanguageTemplatePart
  };
};
/* harmony default export */ const use_create_translation = ((/* unused pure expression or super */ null && (useCreateTranslation)));
;// ./js/src/editors/common/components/translations-table/site-editor/index.js
/* unused harmony import specifier */ var site_editor_coreDataStore;
/* unused harmony import specifier */ var site_editor_select;
/* unused harmony import specifier */ var site_editor_TranslationInputCell;
/* unused harmony import specifier */ var site_editor_AddOrEditCell;
/* unused harmony import specifier */ var site_editor_DeleteCell;
/* unused harmony import specifier */ var site_editor_DefaultLanguageCell;
/* unused harmony import specifier */ var site_editor_EditButton;
/* unused harmony import specifier */ var site_editor_AddButton;
/* unused harmony import specifier */ var site_editor_DeleteWithConfirmation;
/* unused harmony import specifier */ var site_editor_TranslationRow;
/* unused harmony import specifier */ var site_editor_useCreateTranslation;
/* unused harmony import specifier */ var site_editor_TranslationsTableWrapper;
/* unused harmony import specifier */ var site_editor_jsx;
/* unused harmony import specifier */ var site_editor_jsxs;
/**
 * WordPress Dependencies.
 */



/**
 * Internal Dependencies.
 */







/**
 * Add or edit button component.
 *
 * @param {Object}   props                         The component props.
 * @param {boolean}  props.canUpdate               Whether the user can update the translation.
 * @param {boolean}  props.canCreate               Whether the user can create the translation.
 * @param {boolean}  props.canRead                 Whether the user can read the translation.
 * @param {Object}   props.language                The language object.
 * @param {Object}   props.currentPost             The current post.
 * @param {Object}   props.translation             The translation post object.
 * @param {Function} props.handleCreateTranslation The function to handle the create translation.
 * @return {React.Component} The Add or Edit button component.
 */

const site_editor_AddOrEditButton = ({
  canUpdate,
  canCreate,
  canRead,
  language,
  currentPost,
  translation,
  handleCreateTranslation
}) => {
  if (canUpdate || canRead) {
    // Show Edit button for existing translations (disabled if user can only read).
    return /*#__PURE__*/site_editor_jsx(site_editor_EditButton, {
      href: `#`,
      disabled: !canUpdate,
      language: language,
      handleEditClick: () => {
        location.href = location.href.replace(encodeURIComponent(currentPost.id), encodeURIComponent(translation.id));
      }
    });
  }
  return /*#__PURE__*/site_editor_jsx(site_editor_AddButton, {
    href: `#`,
    disabled: !canCreate,
    language: language,
    handleAddClick: () => {
      handleCreateTranslation();
    }
  });
};

/**
 * Site Editor Translations Table component.
 *
 * @param {Object}   props                           The component props.
 * @param {Map}      props.translationsTable         The translations table, contains language object as key and post object as value.
 * @param {Object}   props.currentPost               The current post.
 * @param {Function} props.translationsTableDispatch The translations table dispatch function.
 * @return {React.Component} The Site Editor Translations Table component.
 */
const SiteEditorTranslationsTable = ({
  translationsTable,
  currentPost,
  translationsTableDispatch
}) => {
  const {
    handleCreateTranslation,
    persistDefaultLanguageTemplatePart
  } = site_editor_useCreateTranslation();
  const table = [];
  translationsTable.forEach((translation, language) => {
    // Don't display current post in the translation table.
    if (currentPost?.lang === language.slug) {
      return;
    }
    function onDeleteSuccess() {
      translationsTableDispatch({
        type: 'remove_translation',
        lang: language
      });
    }
    const canCreate = site_editor_select(site_editor_coreDataStore).canUser('create', {
      kind: 'postType',
      name: currentPost.type
    });
    const canUpdate = translation && site_editor_select(site_editor_coreDataStore).canUser('update', {
      kind: 'postType',
      name: currentPost.type,
      id: currentPost.id
    });
    table.push(/*#__PURE__*/site_editor_jsx("tr", {
      children: /*#__PURE__*/site_editor_jsxs(site_editor_TranslationRow, {
        language: language,
        children: [/*#__PURE__*/site_editor_jsx(site_editor_TranslationInputCell, {
          children: /*#__PURE__*/site_editor_jsx("span", {
            className: "pll-translation-language",
            children: language.name
          })
        }), /*#__PURE__*/site_editor_jsx(site_editor_AddOrEditCell, {
          children: /*#__PURE__*/site_editor_jsx(site_editor_AddOrEditButton, {
            canUpdate: canUpdate,
            canCreate: canCreate,
            canRead: !!translation,
            language: language,
            currentPost: currentPost,
            translation: translation,
            handleCreateTranslation: () => {
              if (!language.is_default && 'wp_template_part' === currentPost.type && !currentPost.wp_id) {
                // Ensure the template part in default language exists before creating the translation.
                persistDefaultLanguageTemplatePart(currentPost).then(defaultLangTemplatePart => {
                  handleCreateTranslation(language, defaultLangTemplatePart);
                });
                return;
              }
              handleCreateTranslation(language, currentPost);
            }
          })
        }), /*#__PURE__*/site_editor_jsx(site_editor_DeleteCell, {
          children: /*#__PURE__*/site_editor_jsx(site_editor_DeleteWithConfirmation, {
            post: translation,
            language: language,
            onDeleteSuccess: onDeleteSuccess
          })
        }), /*#__PURE__*/site_editor_jsx(site_editor_DefaultLanguageCell, {
          isDefault: language.is_default
        })]
      })
    }, language.slug));
  });
  return /*#__PURE__*/site_editor_jsx(site_editor_TranslationsTableWrapper, {
    children: table
  });
};
/* harmony default export */ const site_editor = ((/* unused pure expression or super */ null && (SiteEditorTranslationsTable)));
;// ./js/src/editors/common/components/translations-table/index.js
/**
 * Translations table components.
 */



;// ./js/src/editors/common/store/utils.js
/* unused harmony import specifier */ var subscribe;
/* unused harmony import specifier */ var utils_select;
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
  if ((0,external_lodash_.isNil)((0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY))) {
    return Promise.reject("Polylang languages panel can't be initialized because block editor isn't fully initialized.");
  }

  // save url params especially when a new translation is creating
  saveURLParams();

  /**
   * Set a promise fulfilled with the current post.
   */
  const isCurrentPostLoaded = new Promise(function (resolve) {
    const unsubscribe = (0,external_this_wp_data_.subscribe)(function () {
      const currentPost = (0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY).getCurrentPost();
      if (!(0,external_lodash_.isEmpty)(currentPost)) {
        unsubscribe();
        resolve(currentPost);
      }
    });
  });

  /**
   * Set a promise fulfilled with the source post when a new draft is created from a source, null otherwise.
   */
  const isFromPostLoaded = new Promise(function (resolve) {
    const unsubscribe = (0,external_this_wp_data_.subscribe)(function () {
      const fromPostUrlParams = (0,external_this_wp_data_.select)(MODULE_KEY).getFromPost();
      if (!fromPostUrlParams || !fromPostUrlParams.id || !fromPostUrlParams.postType) {
        unsubscribe();
        resolve(null);
        return;
      }
      const fromPost = (0,external_this_wp_data_.select)(MODULE_CORE_KEY).getEntityRecord('postType', fromPostUrlParams.postType, fromPostUrlParams.id, {
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
      (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).editPost({
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
    (0,external_this_wp_data_.dispatch)(MODULE_KEY).setFromPost({
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
  const siteEditorSelector = (0,external_this_wp_data_.select)(MODULE_SITE_EDITOR_KEY);

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
  return null === editedContext ? null : (0,external_this_wp_data_.select)(MODULE_CORE_KEY).getEntityRecord('postType', editedContext.postType, editedContext.postId);
};
;// ./js/src/editors/common/utils.js
/* unused harmony import specifier */ var isBoolean;
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
  const postTypes = (0,external_this_wp_data_.select)('core').getEntitiesConfig('postType');
  const postType = (0,external_lodash_.find)(postTypes, {
    name
  });
  return postType?.baseURL;
}

/**
 * Gets all query string parameters and convert them in a URLSearchParams object.
 *
 * @return {URLSearchParams|null} Search parameters object, null if none.
 */
function getSearchParams() {
  // Variable window.location.search is just read for creating and returning a URLSearchParams object to be able to manipulate it more easily.
  // eslint-disable-next-line prettier/prettier
  if (!(0,external_lodash_.isEmpty)(window.location.search)) {
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
  const languages = (0,external_this_wp_data_.select)(MODULE_KEY).getLanguages();
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
  const languages = (0,external_this_wp_data_.select)(MODULE_KEY).getLanguages();
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
  if (!(0,external_lodash_.isNil)(options.data)) {
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
  const postTypeURLs = (0,external_lodash_.map)((0,external_this_wp_data_.select)('core').getEntitiesConfig('postType'), (0,external_lodash_.property)('baseURL'));

  // Id from the post currently edited.
  const postId = (0,external_this_wp_data_.select)('core/editor').getCurrentPostId();

  // Id from the REST request.
  // options.data never isNil here because it's already verified before in isSaveRequest() function.
  const id = options.data.id;

  // Return true
  // if REST request baseURL matches with one of the known post type baseURLs
  // and the id from the post currently edited corresponds on the id passed to the REST request
  // Return false otherwise
  return -1 !== postTypeURLs.findIndex(function (element) {
    return new RegExp(`${(0,external_lodash_.escapeRegExp)(element)}`).test(options.path);
  }) && postId === id;
}

/**
 * Checks if the given REST request is for the creation of a new template part translation.
 *
 * @param {Object} options The initial request.
 * @return {boolean} True if the request concerns a template part translation creation.
 */
function isTemplatePartTranslationCreationRequest(options) {
  return 'POST' === options.method && options.path.match(/^\/wp\/v2\/template-parts(?:\/|\?|$)/) && !(0,external_lodash_.isNil)(options.data.from_post) && !(0,external_lodash_.isNil)(options.data.lang);
}

/**
 * Checks if the given REST request is for the creation of a new template part.
 *
 * @param {Object} options The initial request.
 * @return {boolean} True if the request concerns a template part creation.
 */
function isNewTemplatePartCreationRequest(options) {
  return 'POST' === options.method && options.path.match(/^\/wp\/v2\/template-parts(?:\/|\?|$)/) && (0,external_lodash_.isNil)(options.data.from_post) && (0,external_lodash_.isNil)(options.data.lang);
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
  options.path = (0,external_this_wp_url_.addQueryArgs)(options.path, {
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
  const isEditingTemplate = (0,external_this_wp_data_.select)(MODULE_POST_EDITOR_KEY)?.isEditingTemplate();
  if ('wp_template_part' === postType && !(0,external_lodash_.isNil)(postId) || isEditingTemplate) {
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
    return (0,external_this_wp_data_.select)(MODULE_SITE_EDITOR_KEY).getEditedPostType();
  }
  return (0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY).getCurrentPostType();
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
  if ((0,external_lodash_.isUndefined)((0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY))) {
    // Return ASAP to avoid issues later.
    return pll_block_editor_plugin_settings.lang.slug;
  }

  // Post block editor case.
  const postLanguage = (0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY).getEditedPostAttribute('lang');
  if (!(0,external_lodash_.isUndefined)(postLanguage) && postLanguage) {
    return postLanguage;
  }

  // Returns the default lang if the current location is a template part list
  // and update pll_block_editor_plugin_settings at the same time.
  const params = new URL(document.location).searchParams;
  const postType = params.get('postType');
  const postId = params.get('postId');
  if ('wp_template_part' === postType && (0,external_lodash_.isNil)(postId)) {
    pll_block_editor_plugin_settings.lang = getDefaultLanguage();
    return pll_block_editor_plugin_settings.lang.slug;
  }

  // FSE template editor case.
  const template = getCurrentPostFromDataStore();
  const templateLanguage = template?.lang;
  if (!(0,external_lodash_.isUndefined)(templateLanguage) && templateLanguage) {
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
  if ((0,external_lodash_.isUndefined)(restBaseUrl)) {
    // The user hasn't the rights to edit template part.
    return;
  }
  const templatePartURLRegExp = new RegExp((0,external_lodash_.escapeRegExp)(restBaseUrl));
  if ('POST' === options.method && templatePartURLRegExp.test(options.path)) {
    const languages = (0,external_this_wp_data_.select)(MODULE_KEY).getLanguages();
    const language = languages.get(langSlug);
    if (!language.is_default) {
      // No suffix for default language.
      const langSuffix = TEMPLATE_PART_SLUG_SEPARATOR + langSlug;
      options.data.slug += langSuffix;
    }
  }
}
;// ./js/src/editors/common/components/metaboxes/site-editor/index.js
/* unused harmony import specifier */ var useReducer;
/* unused harmony import specifier */ var site_editor_useState;
/* unused harmony import specifier */ var useEffect;
/* unused harmony import specifier */ var site_editor_useSelect;
/* unused harmony import specifier */ var getQueryArg;
/* unused harmony import specifier */ var metaboxes_site_editor_coreDataStore;
/* unused harmony import specifier */ var site_editor_LanguageItem;
/* unused harmony import specifier */ var site_editor_MetaboxContainer;
/* unused harmony import specifier */ var site_editor_SiteEditorTranslationsTable;
/* unused harmony import specifier */ var site_editor_getSelectedLanguage;
/* unused harmony import specifier */ var site_editor_getCurrentPostFromDataStore;
/* unused harmony import specifier */ var site_editor_MODULE_KEY;
/* unused harmony import specifier */ var site_editor_UNTRANSLATABLE_POST_TYPE;
/* unused harmony import specifier */ var translationsTableReducer;
/* unused harmony import specifier */ var metaboxes_site_editor_jsxs;
/* unused harmony import specifier */ var metaboxes_site_editor_jsx;
/**
 * WordPress Dependencies.
 */





/**
 * Internal Dependencies.
 */








/**
 * Site Editor Metabox component.
 *
 * @return {React.Component} The Site Editor Metabox component.
 */

const SiteEditorMetabox = () => {
  const [translationTable, tableDispatch] = useReducer(translationsTableReducer, new Map());
  const [currentPost, setCurrentPost] = site_editor_useState({});
  const [selectedLanguage, setSelectedLanguage] = site_editor_useState({});
  const [currentPostType, setCurrentPostType] = site_editor_useState('');
  const languages = site_editor_useSelect(select => select(site_editor_MODULE_KEY).getLanguages());
  useEffect(() => {
    let currentType;
    // Global Styles screen doesn't provide `wp_global_style` as current edited post type.
    if ('/wp_global_styles' === wp.sanitize.stripTagsAndEncodeText(getQueryArg(window.location.href, 'path') // phpcs:ignore WordPressVIPMinimum.JS.Window.location
    )) {
      currentType = 'wp_global_styles';
    }
    // Template context can return a page. So, we need to check post type from the URL.
    if ('wp_template' === wp.sanitize.stripTagsAndEncodeText(getQueryArg(window.location.href, 'postType') // phpcs:ignore WordPressVIPMinimum.JS.Window.location
    )) {
      currentType = 'wp_template';
    }
    if (currentType) {
      setCurrentPostType(currentType);
      return;
    }
    const post = site_editor_getCurrentPostFromDataStore();
    setCurrentPost(post);
    setCurrentPostType(post?.type);
    const language = site_editor_getSelectedLanguage(post?.lang);
    setSelectedLanguage(language);
  }, [setCurrentPost, setCurrentPostType, setSelectedLanguage, languages, translationTable]);
  const translationsData = site_editor_useSelect(select => {
    if (!currentPost || !currentPost.translations) {
      return {};
    }
    const results = {};
    if ('wp_template_part' !== currentPost.type) {
      const translatedPosts = select(metaboxes_site_editor_coreDataStore).getEntityRecords('postType', currentPost.type, {
        include: Object.values(currentPost.translations),
        status: 'any',
        context: 'view',
        per_page: Object.values(currentPost.translations).length,
        lang: '' // Disables language filter middleware.
      });
      translatedPosts?.forEach(post => {
        results[post.lang] = post;
      });
    } else {
      // Template parts cannot be fetched all at once.
      new Map(Object.entries(currentPost.translations)).forEach((translationId, lang) => {
        const postsData = select(metaboxes_site_editor_coreDataStore).getEntityRecords('postType', 'wp_template_part', {
          wp_id: translationId
        });
        if (postsData && postsData.length > 0) {
          results[lang] = postsData[0];
        }
      });
    }
    return results;
  }, [currentPost]);
  useEffect(() => {
    tableDispatch({
      type: 'set_table',
      languages,
      translations: translationsData
    });
  }, [translationsData, languages, tableDispatch]);
  if (translationTable.size === 0) {
    return null;
  }
  return /*#__PURE__*/metaboxes_site_editor_jsxs(site_editor_MetaboxContainer, {
    isError: !selectedLanguage,
    isAllowedPostType: !site_editor_UNTRANSLATABLE_POST_TYPE.includes(currentPostType),
    postType: currentPostType,
    children: [/*#__PURE__*/metaboxes_site_editor_jsx(site_editor_LanguageItem, {
      language: selectedLanguage,
      currentPost: currentPost
    }), /*#__PURE__*/metaboxes_site_editor_jsx(site_editor_SiteEditorTranslationsTable, {
      translationsTable: translationTable,
      currentPost: currentPost,
      translationsTableDispatch: tableDispatch
    })]
  });
};
/* harmony default export */ const metaboxes_site_editor = ((/* unused pure expression or super */ null && (SiteEditorMetabox)));
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

;// ./js/src/editors/common/components/switcher/utils.js
/**
 * WordPress dependencies
 */





/**
 * Internal dependencies
 */


/**
 * Persists language change.
 *
 * @param {Object} newLanguage         New language.
 * @param {Object} newTranslationsData New translations data.
 * @return {Promise} Save post promise.
 */
const saveLanguageChange = (newLanguage, newTranslationsData) => {
  return Promise.allSettled([(0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).editPost(newTranslationsData), (0,external_this_wp_data_.dispatch)(MODULE_CORE_EDITOR_KEY).savePost(),
  // Need to save post to recalculate permalink.
  forceLanguageSave(newLanguage.slug)]).then(() => {
    (0,external_this_wp_data_.dispatch)(external_this_wp_coreData_.store).invalidateResolutionForStoreSelector('getEntityRecord');
    (0,external_this_wp_data_.dispatch)(external_this_wp_coreData_.store).invalidateResolutionForStoreSelector('getEntityRecords');
    (0,external_this_wp_data_.dispatch)(external_this_wp_coreData_.store).invalidateResolutionForStoreSelector('getMedia');
  });
};

/**
 * Tells whether the edited post is empty.
 *
 * @return {boolean} True if the edited post is empty, false otherwise.
 */
const isEditedPostEmpty = () => {
  const editor = (0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY);
  return !editor.getEditedPostAttribute('title') && !editor.getEditedPostContent() && !editor.getEditedPostAttribute('excerpt');
};

/**
 * Forces the save of the post in the new language even if no content has been written.
 * So the post metadata are correctly updated (e.g.: Attachable Medias).
 *
 * @since 3.0
 *
 * @param {string} lang A language slug.
 * @return {Promise} Save post promise.
 */
const forceLanguageSave = lang => {
  const editor = (0,external_this_wp_data_.select)(MODULE_CORE_EDITOR_KEY);
  if (isEditedPostEmpty()) {
    return external_this_wp_apiFetch_default()({
      path: (0,external_this_wp_url_.addQueryArgs)(`wp/v2/posts/${editor.getCurrentPostId()}`, {
        lang
      }),
      method: 'POST'
    });
  }
  return Promise.reject('Force save not required.');
};
;// ./js/src/editors/common/components/switcher/index.js
/**
 * Internal dependencies
 */


/**
 * WordPress dependencies
 */






/**
 * Internal dependencies
 */



const Switcher = ({
  currentPost
}) => {
  const [isOpen, setOpen] = (0,external_this_wp_element_.useState)(false);
  const [selectedLang, setSelectedLang] = (0,external_this_wp_element_.useState)(null);
  const {
    createSuccessNotice,
    createErrorNotice
  } = (0,external_this_wp_data_.useDispatch)(external_this_wp_notices_.store);
  const languages = use_authorized_languages();
  (0,external_this_wp_element_.useEffect)(() => {
    setSelectedLang(languages.get(currentPost.lang));
  }, [currentPost, languages]);
  const prevLangRef = (0,external_this_wp_element_.useRef)(selectedLang);
  (0,external_this_wp_element_.useEffect)(() => {
    prevLangRef.current = languages.get(currentPost.lang);
  }, [languages, currentPost]);
  const openModal = newLangSlug => {
    const newLang = languages.get(newLangSlug);
    setSelectedLang(newLang);
    if (isEditedPostEmpty()) {
      confirmChange(newLang);
      return;
    }
    setOpen(true);
  };
  const closeModal = () => setOpen(false);
  const confirmChange = _selectedLang => {
    closeModal();
    if (!_selectedLang) {
      createErrorNotice((0,external_this_wp_i18n_.__)('Failed to save selected language', 'polylang-pro'), {
        type: 'snackbar'
      });
      return;
    }
    const newTranslations = {
      ...currentPost.translations
    };
    delete newTranslations[prevLangRef.current.slug];

    // Only add the new language if it is not already assigned.
    if (!newTranslations.hasOwnProperty(selectedLang.slug)) {
      newTranslations[selectedLang.slug] = currentPost.id;
    }
    const synchronizedPosts = {
      ...currentPost.synchronizedPosts
    };
    delete synchronizedPosts[prevLangRef.current.slug];
    saveLanguageChange(_selectedLang, {
      lang: _selectedLang.slug,
      translations: newTranslations,
      pll_sync_post: synchronizedPosts
    }).then(() => {
      createSuccessNotice((0,external_this_wp_i18n_.__)('Language changed', 'polylang-pro'), {
        type: 'snackbar'
      });
      pll_block_editor_plugin_settings.lang = _selectedLang;
      document.dispatchEvent(new CustomEvent('onPostLangChoice', {
        detail: {
          lang: _selectedLang
        }
      }));
    });
  };
  const abortChange = () => {
    setSelectedLang(prevLangRef.current);
    closeModal();
  };
  return /*#__PURE__*/(0,jsx_runtime.jsxs)(jsx_runtime.Fragment, {
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)("p", {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)("strong", {
        children: (0,external_this_wp_i18n_.__)('Language', 'polylang-pro')
      })
    }), /*#__PURE__*/(0,jsx_runtime.jsx)("label", {
      className: "screen-reader-text",
      htmlFor: "pll_post_lang_choice",
      children: (0,external_this_wp_i18n_.__)('Language', 'polylang-pro')
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(LanguageDropdown, {
      selectedLanguage: selectedLang,
      handleChange: openModal,
      languages: languages
    }), isOpen && /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_components_.Modal, {
      title: (0,external_this_wp_i18n_.__)('Change language', 'polylang-pro'),
      onRequestClose: abortChange,
      children: [/*#__PURE__*/(0,jsx_runtime.jsx)("p", {
        children: (0,external_this_wp_i18n_.__)('Are you sure you want to change the language of the current content?', 'polylang-pro')
      }), /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
        role: "group",
        className: "components-button-group buttons",
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
          variant: "tertiary",
          onClick: abortChange,
          type: "button",
          children: (0,external_this_wp_i18n_.__)('Cancel', 'polylang-pro')
        }), /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
          children: "\xA0"
        }), /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Button, {
          variant: "primary",
          onClick: () => confirmChange(selectedLang),
          type: "submit",
          children: (0,external_this_wp_i18n_.__)('Change', 'polylang-pro')
        })]
      })]
    })]
  });
};
/* harmony default export */ const switcher = (Switcher);
;// ./js/src/editors/common/components/metaboxes/translations-table-reducer/index.js
/**
 * Reducer for the translations table.
 *
 * @param {Map}    state  The current state.
 * @param {Object} action The action to perform.
 * @return {Map} The new state.
 */
const translations_table_reducer_translationsTableReducer = (state, action) => {
  switch (action.type) {
    case 'add_translation':
      state.set(action.lang, action.post);
      return new Map(state);
    case 'remove_translation':
      state.set(action.lang, null);
      return new Map(state);
    case 'set_table':
      return normalizeTranslations(action.languages, action.translations);
    default:
      throw new Error('Invalid action');
  }
};

/**
 * Normalizes the translations table.
 *
 * @param {Array}  languages    The list of language objects.
 * @param {Object} translations The translations IDs keyed by language slug.
 * @return {Map} The normalized translations table.
 */
const normalizeTranslations = (languages, translations) => {
  const translationsTable = new Map();
  languages.forEach(language => {
    translationsTable.set(language, translations[language.slug] ?? null);
  });
  return translationsTable;
};
;// ./js/src/editors/common/components/metaboxes/post-editor/index.js
/**
 * WordPress Dependencies.
 */



/**
 * Internal Dependencies.
 */







const PostEditorMetabox = () => {
  const [translationsTable, tableDispatch] = (0,external_this_wp_element_.useReducer)(translations_table_reducer_translationsTableReducer, new Map());
  const languages = (0,external_this_wp_data_.useSelect)(select => select(MODULE_KEY).getLanguages());
  const {
    currentPost,
    selectedLanguage,
    isAllowedPostType
  } = (0,external_this_wp_data_.useSelect)(select => {
    const post = select(MODULE_CORE_EDITOR_KEY).getCurrentPost();
    const lang = select(MODULE_CORE_EDITOR_KEY).getEditedPostAttribute('lang');
    const isAllowed = !UNTRANSLATABLE_POST_TYPE.includes(post?.type);
    const language = getSelectedLanguage(lang);
    return {
      currentPost: post,
      selectedLanguage: language,
      isAllowedPostType: isAllowed
    };
  }, []);
  const translationsData = (0,external_this_wp_data_.useSelect)(select => {
    if (!currentPost || !currentPost.translations) {
      return {};
    }
    if ('auto-draft' === currentPost.status) {
      const fromPostUrlParams = select(MODULE_KEY).getFromPost();
      if (fromPostUrlParams) {
        const fromPostData = select(external_this_wp_coreData_.store).getEntityRecord('postType', currentPost.type, fromPostUrlParams.id, {
          context: 'view'
        } // Use 'view' context so translators can read posts they cannot edit.
        );
        if (fromPostData) {
          currentPost.translations = {
            ...fromPostData.translations,
            [currentPost.lang]: currentPost.id
          };
        }
      }
    }
    const results = {};
    const translatedPosts = select(external_this_wp_coreData_.store).getEntityRecords('postType', currentPost.type, {
      include: Object.values(currentPost.translations),
      context: 'view',
      status: 'any',
      per_page: Object.values(currentPost.translations).length,
      lang: '' // Disables language filter middleware.
    });
    translatedPosts?.forEach(post => {
      const translationData = getTranslationData(post.lang, translationsTable);

      /*
       * Preserve the optimistic placeholder (id: 0) until the server returns the real translation ID,
       * then let the real entity record be fetched.
       */
      if (translationData && translationData.id === post.id) {
        results[post.lang] = translationData;
        return;
      }
      results[post.lang] = post;
    });
    return results;
  }, [currentPost, translationsTable]);
  (0,external_this_wp_element_.useEffect)(() => {
    tableDispatch({
      type: 'set_table',
      languages,
      translations: translationsData
    });
  }, [translationsData, languages, tableDispatch]);
  if (translationsTable.size === 0) {
    return null;
  }
  return /*#__PURE__*/(0,jsx_runtime.jsxs)(container, {
    isError: !selectedLanguage,
    isAllowedPostType: isAllowedPostType,
    postType: currentPost?.type,
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(switcher, {
      currentPost: currentPost
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(post_editor, {
      currentPost: currentPost,
      translationsTable: translationsTable,
      selectedLanguage: selectedLanguage,
      tableDispatch: tableDispatch
    })]
  });
};

/**
 * Gets data from the translations table map for a given language.
 *
 * @param {string} langSlug          The language slug.
 * @param {Map}    translationsTable The translations table.
 * @return {Object|null} The translation data or null if not found.
 */
const getTranslationData = (langSlug, translationsTable) => {
  for (const [lang, post] of translationsTable) {
    if (lang.slug === langSlug) {
      return post;
    }
  }
  return null;
};
/* harmony default export */ const metaboxes_post_editor = (PostEditorMetabox);
;// ./js/src/editors/common/components/metaboxes/index.js
/**
 * Metabox components.
 */



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
;// ./js/src/editors/post/pro-features/index.js
/**
 * Registers Polylang Pro features for the metabox in the post editor.
 */

/**
 * WordPress dependencies
 */



/**
 * Internal dependencies
 */






/**
 * Registers the Pro features for the post editor metabox.
 *
 * @return {void}
 */

const registerProFeatures = () => {
  (0,external_this_wp_plugins_.registerPlugin)('pll-machine-translation', {
    render: () => /*#__PURE__*/(0,jsx_runtime.jsx)(plugin_feature_flag_button, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(machine_translation, {})
    })
  });
  (0,external_this_wp_plugins_.registerPlugin)('pll-duplicate-content', {
    render: () => /*#__PURE__*/(0,jsx_runtime.jsx)(plugin_feature_flag_button, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(duplicate, {})
    })
  });
  const SynchronizationFeatureButton = fillProps => {
    const authorizedLanguages = use_authorized_languages();
    if (!fillProps) {
      return null;
    }
    return /*#__PURE__*/(0,jsx_runtime.jsx)(buttons_synchronization, {
      post: fillProps.translation,
      language: fillProps.language,
      translationTableReducer: fillProps.translationTableReducer,
      disabled: !authorizedLanguages.has(fillProps.language.slug)
    });
  };
  const languages = (0,external_this_wp_data_.select)(MODULE_KEY).getLanguages();
  if (!languages || languages.length === 0) {
    return;
  }
  languages.forEach(language => {
    (0,external_this_wp_plugins_.registerPlugin)(`pll-synchronization-${language.slug}`, {
      render: ({
        fillProps
      }) => /*#__PURE__*/(0,jsx_runtime.jsx)(PluginFeatureTableRowButtonFill, {
        className: "pll-sync-column pll-column-icon",
        languageSlug: language.slug,
        children: /*#__PURE__*/(0,jsx_runtime.jsx)(SynchronizationFeatureButton, {
          fillProps: fillProps
        })
      })
    });
  });
};
;// ./js/src/editors/post/index.js
/**
 * Import styles
 */


/**
 * External dependencies
 */


/**
 * WordPress Dependencies.
 */



/**
 * Internal Dependencies.
 */









editors_requests_filter(addParametersToRequest);
const sidebarName = 'polylang-sidebar';
const settingsErrors = pll_block_editor_plugin_settings?.machine_translation?.errors; // eslint-disable-line no-undef, camelcase

const _root = document.createElement('div');
_root.id = 'pll-root';
const root = document.body.appendChild(_root);
(0,external_this_wp_element_.createRoot)(root).render(/*#__PURE__*/(0,jsx_runtime.jsx)(app, {
  sidebar: () => {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(sidebar, {
      SidebarSlot: external_this_wp_editPost_.PluginSidebar,
      MoreMenuItemSlot: external_this_wp_editPost_.PluginSidebarMoreMenuItem,
      sidebarName: sidebarName,
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(metaboxes_post_editor, {})
    });
  },
  sidebarName: sidebarName,
  onPromise: isBlockPostEditorContextInitialized,
  children: undefined !== settingsErrors && /*#__PURE__*/(0,jsx_runtime.jsx)(display_notices, {
    notices: settingsErrors
  })
}));
isBlockPostEditorContextInitialized().then(() => {
  registerProFeatures();
});
})();

this["polylang-pro"] = __webpack_exports__;
/******/ })()
;