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

/***/ 89
(module) {

module.exports = (function() { return this["wp"]["blockEditor"]; }());

/***/ },

/***/ 545
(module) {

module.exports = (function() { return this["wp"]["blocks"]; }());

/***/ },

/***/ 959
(module) {

module.exports = (function() { return this["wp"]["components"]; }());

/***/ },

/***/ 987
(module) {

module.exports = (function() { return this["wp"]["data"]; }());

/***/ },

/***/ 2
(module) {

module.exports = (function() { return this["wp"]["editor"]; }());

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

// EXTERNAL MODULE: external {"this":["wp","blocks"]}
var external_this_wp_blocks_ = __webpack_require__(545);
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
// EXTERNAL MODULE: external {"this":["wp","i18n"]}
var external_this_wp_i18n_ = __webpack_require__(75);
// EXTERNAL MODULE: external {"this":["wp","blockEditor"]}
var external_this_wp_blockEditor_ = __webpack_require__(89);
// EXTERNAL MODULE: external {"this":["wp","components"]}
var external_this_wp_components_ = __webpack_require__(959);
// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(419);
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher-edit.js
/**
 * Language switcher block edit.
 */

/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */


const i18nAttributeStrings = pll_block_editor_blocks_settings;
function createLanguageSwitcherEdit(props) {
  const createToggleAttribute = function (propName) {
    return () => {
      const value = props.attributes[propName];
      const {
        setAttributes
      } = props;
      let updatedAttributes = {
        [propName]: !value
      };
      let forcedAttributeName;
      let forcedAttributeUnchecked;

      // Both show_names and show_flags attributes can't be unchecked together.
      switch (propName) {
        case 'show_names':
          forcedAttributeName = 'show_flags';
          forcedAttributeUnchecked = !props.attributes[forcedAttributeName];
          break;
        case 'show_flags':
          forcedAttributeName = 'show_names';
          forcedAttributeUnchecked = !props.attributes[forcedAttributeName];
          break;
      }
      if ('show_names' === propName || 'show_flags' === propName) {
        if (value && forcedAttributeUnchecked) {
          updatedAttributes = (0,external_lodash_.assign)(updatedAttributes, {
            [forcedAttributeName]: forcedAttributeUnchecked
          });
        }
      }
      setAttributes(updatedAttributes);
    };
  };
  const toggleDropdown = createToggleAttribute('dropdown');
  const toggleShowNames = createToggleAttribute('show_names');
  const toggleShowFlags = createToggleAttribute('show_flags');
  const toggleForceHome = createToggleAttribute('force_home');
  const toggleHideCurrent = createToggleAttribute('hide_current');
  const toggleHideIfNoTranslation = createToggleAttribute('hide_if_no_translation');
  const {
    dropdown,
    show_names,
    show_flags,
    force_home,
    hide_current,
    hide_if_no_translation
  } = props.attributes;
  function ToggleControlDropdown() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.dropdown,
      checked: dropdown,
      onChange: toggleDropdown
    });
  }
  function ToggleControlShowNames() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.show_names,
      checked: show_names // eslint-disable-line camelcase
      ,
      onChange: toggleShowNames
    });
  }
  function ToggleControlShowFlags() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.show_flags,
      checked: show_flags // eslint-disable-line camelcase
      ,
      onChange: toggleShowFlags
    });
  }
  function ToggleControlForceHome() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.force_home,
      checked: force_home // eslint-disable-line camelcase
      ,
      onChange: toggleForceHome
    });
  }
  function ToggleControlHideCurrent() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.hide_current,
      checked: hide_current // eslint-disable-line camelcase
      ,
      onChange: toggleHideCurrent
    });
  }
  function ToggleControlHideIfNoTranslations() {
    return /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.ToggleControl, {
      label: i18nAttributeStrings.hide_if_no_translation,
      checked: hide_if_no_translation // eslint-disable-line camelcase
      ,
      onChange: toggleHideIfNoTranslation
    });
  }
  return {
    ToggleControlDropdown,
    ToggleControlShowNames,
    ToggleControlShowFlags,
    ToggleControlForceHome,
    ToggleControlHideCurrent,
    ToggleControlHideIfNoTranslations
  };
}
// EXTERNAL MODULE: external {"this":["wp","element"]}
var external_this_wp_element_ = __webpack_require__(601);
;// ./vendor/wpsyntex/polylang/js/src/blocks/languages-context.js
/**
 * WordPress dependencies.
 */


/**
 * Context for the languages.
 *
 * @type {React.Context<null>}
 */
const LanguagesContext = (0,external_this_wp_element_.createContext)(null);
;// ./vendor/wpsyntex/polylang/js/src/blocks/hooks/use-memoized-switcher-label.js
/**
 * WordPress dependencies
 */
// '@wordpress/element' is provided by WordPress core.
// eslint-disable-next-line import/no-extraneous-dependencies


/**
 * Hook to memoize the switcher label.
 *
 * @param {Object}  language  The language object.
 * @param {boolean} showFlags Whether to show the flags.
 * @param {boolean} showNames Whether to show the names.
 * @return {Object} The memoized switcher label containing the text and the flag.
 */
const useMemoizedSwitcherLabel = (language, showFlags, showNames) => {
  const {
    text,
    flag
  } = (0,external_this_wp_element_.useMemo)(() => {
    let memoizedText = '';
    if (showNames) {
      if (showFlags) {
        memoizedText = ` ${language.name}`;
      } else {
        memoizedText = language.name;
      }
    }
    const memoizedFlag = showFlags ? language.flag : '';
    return {
      text: memoizedText,
      flag: memoizedFlag
    };
  }, [language, showFlags, showNames]);
  return {
    text,
    flag
  };
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher/components/switcher-list-element.js
/**
 * Internal dependencies
 */


/**
 * Switcher list element component.
 *
 * @param {Object}  props           Component props.
 * @param {Object}  props.language  Language object.
 * @param {boolean} props.showFlags Whether to show the flags.
 * @param {boolean} props.showNames Whether to show the names.
 * @return {ReactElement}            The Switcher element component.
 */

const SwitcherListElement = ({
  language,
  showFlags,
  showNames
}) => {
  const {
    text,
    flag
  } = useMemoizedSwitcherLabel(language, showFlags, showNames);
  return /*#__PURE__*/(0,jsx_runtime.jsxs)("li", {
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)("span", {
      dangerouslySetInnerHTML: {
        __html: flag
      }
    }), " ", text, " "]
  });
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher/components/switcher-ui.js
/**
 * Internal dependencies
 */


/**
 * Switcher UI component.
 *
 * @param {Object}  props            The component props.
 * @param {Array}   props.languages  The languages to display.
 * @param {boolean} props.showFlags  Whether to show the flags.
 * @param {boolean} props.showNames  Whether to show the names.
 * @param {boolean} props.isDropdown Whether to show the dropdown.
 * @return {ReactElement} The Switcher UI component.
 */

const SwitcherUI = ({
  languages,
  showFlags,
  showNames,
  isDropdown
}) => {
  if (isDropdown) {
    return /*#__PURE__*/(0,jsx_runtime.jsx)("select", {
      children: languages.map(language => {
        return /*#__PURE__*/(0,jsx_runtime.jsx)("option", {
          value: language.slug,
          children: language.name
        }, language.slug);
      })
    });
  }
  return /*#__PURE__*/(0,jsx_runtime.jsx)("ul", {
    children: languages.map(language => {
      return /*#__PURE__*/(0,jsx_runtime.jsx)(SwitcherListElement, {
        language: language,
        showFlags: showFlags,
        showNames: showNames
      }, language.slug);
    })
  });
};
// EXTERNAL MODULE: external {"this":["wp","data"]}
var external_this_wp_data_ = __webpack_require__(987);
// EXTERNAL MODULE: external {"this":["wp","editor"]}
var external_this_wp_editor_ = __webpack_require__(2);
;// ./node_modules/@wpsyntex/polylang-react-library/build/hooks/use-current-language.js
/**
 * WordPress dependencies
 */

// This package is not found in `@wordpress/scripts` like others (peer dependency).
// eslint-disable-next-line import/no-unresolved


/**
 * Custom hook to get the current language from the editor.
 *
 * @param {Array} languages The languages list.
 * @return {Object|null} The current language, `null` if not found.
 */
const useCurrentLanguage = languages => {
  const currentPost = (0,external_this_wp_data_.useSelect)(select => select(external_this_wp_editor_.store).getCurrentPost());
  if (!languages || !currentPost) {
    return null;
  }
  const currentLanguageSlug = currentPost.lang ?? pllEditorCurrentLanguageSlug; // eslint-disable-line no-undef

  const currentLanguage = languages.find(language => {
    return language.slug === currentLanguageSlug;
  });
  return currentLanguage ? currentLanguage : null;
};
;// ./node_modules/@wpsyntex/polylang-react-library/build/hooks/use-curated-languages.js
/**
 * WordPress dependencies
 */


/**
 * Hook to curate the languages.
 * Returns an array of languages ensuring that the current language is always the first one.
 *
 * @param {Object[]} languages       The languages.
 * @param {Object}   currentLanguage The current language.
 * @param {boolean}  reduceToOneItem Whether to reduce the languages to one item.
 * @return {Object[]} The curated languages.
 */
const useCuratedLanguages = (languages, currentLanguage, reduceToOneItem) => {
  const curatedLanguages = (0,external_this_wp_element_.useMemo)(() => {
    if (!currentLanguage) {
      return [];
    }
    if (reduceToOneItem) {
      return [currentLanguage];
    }
    return [currentLanguage, ...languages.filter(language => {
      return language.slug !== currentLanguage.slug;
    })];
  }, [languages, currentLanguage, reduceToOneItem]);
  return curatedLanguages;
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher/components/switcher-container.js
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Switcher container component.
 *
 * @param {Object} props            The component props.
 * @param {Object} props.attributes The block attributes.
 * @return {ReactElement} The Switcher component.
 */

const SwitcherContainer = ({
  attributes
}) => {
  const {
    dropdown,
    show_flags,
    show_names
  } = attributes;
  const {
    languages
  } = (0,external_this_wp_element_.useContext)(LanguagesContext);
  const currentLanguage = useCurrentLanguage(languages);
  const curatedLanguages = useCuratedLanguages(languages, currentLanguage, dropdown);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(SwitcherUI, {
    languages: curatedLanguages,
    showFlags: show_flags,
    showNames: show_names,
    isDropdown: dropdown
  });
};
// EXTERNAL MODULE: external {"this":["wp","apiFetch"]}
var external_this_wp_apiFetch_ = __webpack_require__(631);
var external_this_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_this_wp_apiFetch_);
;// ./node_modules/@wpsyntex/polylang-react-library/build/hooks/use-languages-list.js
/**
 * WordPress dependencies
 */



/**
 * Custom hook to get the languages list.
 *
 * @return {Array|null} The languages list, `null` if not loaded yet.
 */
const useLanguagesList = () => {
  const [languages, setLanguages] = (0,external_this_wp_element_.useState)(null);
  (0,external_this_wp_element_.useEffect)(() => {
    external_this_wp_apiFetch_default()({
      path: '/pll/v1/languages',
      method: 'GET'
    }).then(response => setLanguages(response));
  }, []);
  return languages;
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher/edit.js
/**
 * Edit callback for language switcher block.
 */

/**
 * WordPress dependencies
 */




/**
 * Internal dependencies
 */





/**
 * Edit callback for language switcher block.
 *
 * @param {Object} props Block properties.
 * @return {ReactElement} The block content and controls.
 */

const Edit = props => {
  const {
    dropdown
  } = props.attributes;
  const languages = useLanguagesList();
  const {
    ToggleControlDropdown,
    ToggleControlShowNames,
    ToggleControlShowFlags,
    ToggleControlForceHome,
    ToggleControlHideCurrent,
    ToggleControlHideIfNoTranslations
  } = createLanguageSwitcherEdit(props);
  return /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
    ...(0,external_this_wp_blockEditor_.useBlockProps)(),
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_blockEditor_.InspectorControls, {
      children: /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_components_.PanelBody, {
        title: (0,external_this_wp_i18n_.__)('Language switcher settings', 'polylang'),
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlDropdown, {}), !dropdown && /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlShowNames, {}), !dropdown && /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlShowFlags, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlForceHome, {}), !dropdown && /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlHideCurrent, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlHideIfNoTranslations, {})]
      })
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Disabled, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(LanguagesContext.Provider, {
        value: {
          languages
        },
        children: /*#__PURE__*/(0,jsx_runtime.jsx)(SwitcherContainer, {
          attributes: props.attributes
        })
      })
    })]
  });
};
;// ./vendor/wpsyntex/polylang/src/modules/Blocks/Language_Switcher/Standard/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"polylang/language-switcher"}');
;// ./vendor/wpsyntex/polylang/js/src/blocks/language-switcher/index.js
/**
 * Register language switcher block.
 */

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



(0,external_this_wp_blocks_.registerBlockType)(block_namespaceObject.UU, {
  icon: icons_translation,
  edit: Edit
});
// EXTERNAL MODULE: external {"this":["wp","hooks"]}
var external_this_wp_hooks_ = __webpack_require__(873);
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/components/switcher-link-element.js
/**
 * Internal dependencies
 */


/**
 * Switcher link element component.
 *
 * @param {Object}  props            Component props.
 * @param {Object}  props.language   Language object.
 * @param {boolean} props.isTopLevel Whether the language is the top level language.
 * @param {boolean} props.showFlags  Whether to show the flags.
 * @param {boolean} props.showNames  Whether to show the names.
 * @return {ReactElement}            The Switcher element component.
 */

const SwitcherLinkElement = ({
  language,
  isTopLevel,
  showFlags,
  showNames
}) => {
  const {
    text,
    flag
  } = useMemoizedSwitcherLabel(language, showFlags, showNames);
  const prefix = isTopLevel ? '' : ' ';
  return (
    /*#__PURE__*/
    // eslint-disable-next-line jsx-a11y/anchor-is-valid
    (0,jsx_runtime.jsxs)("a", {
      href: '#',
      children: [prefix, /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
        dangerouslySetInnerHTML: {
          __html: flag
        }
      }), " ", text, " "]
    })
  );
};
;// ./node_modules/@wpsyntex/polylang-react-library/build/icons/submenu.js
/**
 * Submenu icon
 */

/**
 * WordPress dependencies
 */


const submenu_isPrimitivesComponents = 'undefined' !== typeof wp.primitives;
const SubmenuIcon = () => submenu_isPrimitivesComponents ? /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  width: "12",
  height: "12",
  viewBox: "0 0 12 12",
  fill: "none",
  children: /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_primitives_.Path, {
    d: "M1.50002 4L6.00002 8L10.5 4",
    strokeWidth: "1.5"
  })
}) : 'submenu';
/* harmony default export */ const submenu = (SubmenuIcon);
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/components/switcher-ui.js
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */



/**
 * Switcher UI component.
 *
 * @param {Object}  props                 The component props.
 * @param {Array}   props.languages       The languages to display.
 * @param {boolean} props.showFlags       Whether to show the flags.
 * @param {boolean} props.showNames       Whether to show the names.
 * @param {boolean} props.withSubmenuIcon Whether to show the submenu icon.
 * @return {ReactElement} The Switcher UI component.
 */

const switcher_ui_SwitcherUI = ({
  languages,
  showFlags,
  showNames,
  withSubmenuIcon
}) => {
  return /*#__PURE__*/(0,jsx_runtime.jsx)(jsx_runtime.Fragment, {
    children: languages && languages.map(language => {
      return /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_element_.Fragment, {
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(SwitcherLinkElement, {
          language: language,
          isTopLevel: languages.indexOf(language) === 0,
          showFlags: showFlags,
          showNames: showNames
        }), withSubmenuIcon && /*#__PURE__*/(0,jsx_runtime.jsx)("span", {
          className: "wp-block-navigation__submenu-icon",
          children: /*#__PURE__*/(0,jsx_runtime.jsx)(submenu, {})
        })]
      }, language.slug);
    })
  });
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/components/navigation-switcher-container.js
/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */




/**
 * Switcher container component.
 *
 * @param {Object} props            The component props.
 * @param {Object} props.attributes The block attributes.
 * @param {Object} props.context    The block context.
 * @return {ReactElement} The Switcher component.
 */

const NavigationSwitcherContainer = ({
  attributes,
  context
}) => {
  const {
    dropdown,
    show_flags,
    show_names
  } = attributes;
  const {
    showSubmenuIcon,
    openSubmenusOnClick
  } = context;
  const {
    languages
  } = (0,external_this_wp_element_.useContext)(LanguagesContext);
  const currentLanguage = useCurrentLanguage(languages);
  const curatedLanguages = useCuratedLanguages(languages, currentLanguage, dropdown);
  return /*#__PURE__*/(0,jsx_runtime.jsx)(switcher_ui_SwitcherUI, {
    languages: curatedLanguages,
    showFlags: Boolean(show_flags),
    showNames: Boolean(show_names),
    withSubmenuIcon: Boolean((showSubmenuIcon || openSubmenusOnClick) && dropdown)
  });
};
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/edit.js
/**
 * Edit callback for navigation language switcher block.
 */

/**
 * WordPress dependencies
 */




/**
 * Internal dependencies
 */





/**
 * Edit callback for navigation language switcher block.
 *
 * @param {Object} props Block properties.
 * @return {ReactElement} The block content and controls.
 */

const edit_Edit = props => {
  const {
    dropdown
  } = props.attributes;
  const languages = useLanguagesList();
  const {
    ToggleControlDropdown,
    ToggleControlShowNames,
    ToggleControlShowFlags,
    ToggleControlForceHome,
    ToggleControlHideCurrent,
    ToggleControlHideIfNoTranslations
  } = createLanguageSwitcherEdit(props);
  return /*#__PURE__*/(0,jsx_runtime.jsxs)("div", {
    ...(0,external_this_wp_blockEditor_.useBlockProps)(),
    children: [/*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_blockEditor_.InspectorControls, {
      children: /*#__PURE__*/(0,jsx_runtime.jsxs)(external_this_wp_components_.PanelBody, {
        title: (0,external_this_wp_i18n_.__)('Language switcher settings', 'polylang'),
        children: [/*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlDropdown, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlShowNames, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlShowFlags, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlForceHome, {}), !dropdown && /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlHideCurrent, {}), /*#__PURE__*/(0,jsx_runtime.jsx)(ToggleControlHideIfNoTranslations, {})]
      })
    }), /*#__PURE__*/(0,jsx_runtime.jsx)(external_this_wp_components_.Disabled, {
      children: /*#__PURE__*/(0,jsx_runtime.jsx)(LanguagesContext.Provider, {
        value: {
          languages
        },
        children: /*#__PURE__*/(0,jsx_runtime.jsx)(NavigationSwitcherContainer, {
          attributes: props.attributes,
          context: props.context
        })
      })
    })]
  });
};
;// ./vendor/wpsyntex/polylang/src/modules/Blocks/Language_Switcher/Navigation/block.json
const Navigation_block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"polylang/navigation-language-switcher"}');
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/menu-items-converter.js
/**
 * Menu items converter
 */

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */


/**
 * Apply a callback function on each block of the blocks list.
 *
 * @param {Array}  blocks        The list of blocks to process.
 * @param {Array}  menuItems     The initial menu items from where the blocks are converted to.
 * @param {Object} blocksMapping The mapping between the menu items and their corresponding blocks.
 * @param {mapper} mapper        A callback to change the converted block by another one if necessary
 * @return {Array} Array of blocks updated.
 */
function mapBlockTree(blocks, menuItems, blocksMapping, mapper) {
  /**
   * A function to apply to each block to convert it if necessary by applying the `mapper` filter.
   *
   * @param {Object} block The block to replace or not.
   * @return {Object} The new block potentially replaced by the `mapper`.
   */
  const convertBlock = block => ({
    ...mapper(block, menuItems, blocksMapping),
    innerBlocks: mapBlockTree(block.innerBlocks, menuItems, blocksMapping, mapper)
  });
  return blocks.map(convertBlock);
}

/**
 * A filter to detect the `core/navigation-link` block not correctly converted from the language switcher menu item
 * and convert it to its corresponding `polylang/navigation-language-switcher` block.
 *
 * @callback mapper
 * @param {Object} block         The block converted from the menu item.
 * @param {Array}  menuItems     The initial menu items from where the blocks are converted to.
 * @param {Object} blocksMapping The mapping between the menu items and their corresponding blocks.
 * @return {Object} The block correctly converted.
 */
const blocksFilter = (block, menuItems, blocksMapping) => {
  if (block.name === 'core/navigation-link' && block.attributes?.url === '#pll_switcher') {
    const menuItem = menuItems.find(item => item.url === '#pll_switcher'); // Get the corresponding menu item.
    const attributes = menuItem.meta._pll_menu_item; // Get its options.
    const newBlock = (0,external_this_wp_blocks_.createBlock)(Navigation_block_namespaceObject.UU, attributes);
    blocksMapping[menuItem.id] = newBlock.clientId; // Update the blocks mapping.
    return newBlock;
  }
  return block;
};

/**
 * A filter callback hooked to `blocks.navigation.__unstableMenuItemsToBlocks`.
 *
 * @param {Array} blocks    The list of blocks to process.
 * @param {Array} menuItems The initial menu items from where the blocks are converted to.
 * @return {Array} Array of blocks updated.
 */
const menuItemsToBlocksFilter = (blocks, menuItems) => ({
  ...blocks,
  innerBlocks: mapBlockTree(blocks.innerBlocks, menuItems, blocks.mapping, blocksFilter)
});
;// ./vendor/wpsyntex/polylang/js/src/blocks/navigation-language-switcher/index.js
/**
 * Register navigation language switcher block.
 */

/**
 * WordPress Dependencies
 */



/**
 * Internal dependencies
 */




(0,external_this_wp_blocks_.registerBlockType)(Navigation_block_namespaceObject.UU, {
  icon: icons_translation,
  transforms: {
    from: [{
      type: 'block',
      blocks: ['core/navigation-link'],
      transform: () => (0,external_this_wp_blocks_.createBlock)(Navigation_block_namespaceObject.UU)
    }]
  },
  edit: edit_Edit
});

/**
 * Hooks to the classic menu conversion to core/navigation block to be able to convert
 * the language switcher menu item to its corresponding block.
 */
(0,external_this_wp_hooks_.addFilter)('blocks.navigation.__unstableMenuItemsToBlocks', 'polylang/include-language-switcher', menuItemsToBlocksFilter);
;// ./vendor/wpsyntex/polylang/js/src/blocks/index.js
/**
 * Registers Polylang blocks in the editors and enables attributes controls.
 */


})();

this["polylang-pro"] = __webpack_exports__;
/******/ })()
;