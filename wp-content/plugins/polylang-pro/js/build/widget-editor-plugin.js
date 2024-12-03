/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 419:
/***/ ((module) => {

module.exports = (function() { return this["lodash"]; }());

/***/ }),

/***/ 631:
/***/ ((module) => {

module.exports = (function() { return this["wp"]["apiFetch"]; }());

/***/ }),

/***/ 987:
/***/ ((module) => {

module.exports = (function() { return this["wp"]["data"]; }());

/***/ }),

/***/ 172:
/***/ ((module) => {

module.exports = (function() { return this["wp"]["url"]; }());

/***/ })

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
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";

// EXTERNAL MODULE: external {"this":["wp","apiFetch"]}
var external_this_wp_apiFetch_ = __webpack_require__(631);
var external_this_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_this_wp_apiFetch_);
// EXTERNAL MODULE: external {"this":["wp","data"]}
var external_this_wp_data_ = __webpack_require__(987);
// EXTERNAL MODULE: external {"this":["wp","url"]}
var external_this_wp_url_ = __webpack_require__(172);
// EXTERNAL MODULE: external "lodash"
var external_lodash_ = __webpack_require__(419);
;// ./modules/block-editor/js/sidebar/settings.js
/**
 * Module Constants
 *
 * @package Polylang-Pro
 */

const settings_MODULE_KEY = 'pll/metabox';
const settings_MODULE_CORE_EDITOR_KEY = 'core/editor';
const settings_MODULE_SITE_EDITOR_KEY = 'core/edit-site';
const settings_MODULE_POST_EDITOR_KEY = 'core/edit-post';
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
const settings_TEMPLATE_PART_SLUG_SEPARATOR = '___'; // Its value must be synchronized with its equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR
const settings_TEMPLATE_PART_SLUG_CHECK_LANGUAGE_PATTERN = '[a-z_-]+'; // Its value must be synchronized with it equivalent in PHP @see PLL_FSE_Template_Slug::SEPARATOR


;// ./modules/block-editor/js/sidebar/utils.js
/**
 * WordPress Dependencies
 *
 * @package Polylang-Pro
 */





/**
 * Internal dependencies
 */


/**
 * Converts array of object to a map.
 *
 * @param {array} array Array to convert.
 * @param {*}     key   The key in the object used as key to build the map.
 * @returns {Map}
 */
function convertArrayToMap( array, key ){
	const map = new Map();
	array.reduce(
		function (accumulator, currentValue) {
			accumulator.set( currentValue[key], currentValue );
			return accumulator;
		},
		map
	);
	return map;
}

/**
 * Converts map to an associative array.
 *
 * @param {Map} map The map to convert.
 * @returns {Object}
 */
function convertMapToObject( map ){
	const object = {};
	map.forEach(
		function ( value, key, map ) {
			const obj = this;
			this[key] = isBoolean( value ) ? value.toString() : value;
		},
		object
	);
	return object;
}

/**
 * Checks whether the current screen is block-based post type editor.
 *
 * @returns {boolean} True if block editor for post type; false otherwise.
 */
function isPostTypeBlockEditor() {
	return !! document.getElementById( 'editor' );
}

/**
 * Checks whether the current screen is the block-based widgets editor.
 *
 * @returns {boolean} True if we are in the widgets block editor; false otherwise.
 */
function isWidgetsBlockEditor() {
	return !! document.getElementById( 'widgets-editor' );
}

/**
 * Checks whether the current screen is the customizer widgets editor.
 *
 * @returns {boolean} True if we are in the customizer widgets editor; false otherwise.
 */
function isWidgetsCustomizerEditor() {
	return !! document.getElementById( 'customize-controls' );
}


/**
 * Checks whether the current screen is the site editor.
 * Takes in account if Gutenberg is activated.
 *
 * @returns {boolean} True if site editor screen, false otherwise.
 */
function isSiteBlockEditor() {
	return !! ( document.getElementById( 'site-editor' ) || document.getElementById( 'edit-site-editor' ) );
}

/**
 * Returns the post type URL for REST API calls or undefined if the user hasn't the rights.
 *
 * @param {string} name The post type name.
 * @returns {string|undefined}
 */
function getPostsUrl( name ) {
	const postTypes = select( 'core' ).getEntitiesByKind( 'postType' );
	const postType = find( postTypes, { name } );
	return postType?.baseURL;
}

/**
 * Gets all query string parameters and convert them in a URLSearchParams object.
 *
 * @returns {Object}
 */
function	getSearchParams() {
	// Variable window.location.search is just read for creating and returning a URLSearchParams object to be able to manipulate it more easily.
	if ( ! isEmpty( window.location.search ) ) { // phpcs:ignore WordPressVIPMinimum.JS.Window.location
		return new URLSearchParams( window.location.search ); // phpcs:ignore WordPressVIPMinimum.JS.Window.location
	} else {
		return null;
	}
}

/**
 * Gets selected language.
 *
 * @param {string} lang The post language code.
 * @returns {Object} The selected language.
 */
function getSelectedLanguage( lang ) {
	const languages = select( MODULE_KEY ).getLanguages();
	// Pick up this language as selected in languages list
	return languages.get( lang );
}

/**
 * Gets the default language.
 *
 * @returns {Object} The default Language.
 */
function getDefaultLanguage() {
	const languages = select( MODULE_KEY ).getLanguages();
	return Array.from( languages.values() ).find( lang => lang.is_default );
}

/**
 * Checks if the given language is the default one.
 *
 * @param {string} lang The language code to compare with.
 * @returns {boolean} True if the given language is the default one.
 */
function isDefaultLanguage( lang ) {
	return lang === getDefaultLanguage().slug;
}

/**
 * Gets translated posts.
 *
 * @param {Object}                  translations       The translated posts object with language codes as keys and ids as values.
 * @param {Object.<string, Object>} translations_table The translations table data with language codes as keys and data object as values.
 * @returns {Map}
 */
function getTranslatedPosts( translations, translations_table, lang ) {
	const translationsTable = getTranslationsTable( translations_table, lang );
	const fromPost = select( MODULE_KEY ).getFromPost();
	let translatedPosts = new Map( Object.entries( [] ) );
	if ( ! isUndefined( translations ) ) {
		translatedPosts = new Map( Object.entries( translations ) );
	}
	// If we come from another post for creating a new one, we have to update translated posts from the original post
	// to be able to update translations attribute of the post
	if ( ! isNil( fromPost ) && ! isNil( fromPost.id ) ) {
		translationsTable.forEach(
			( translationData, lang ) => {
				if ( ! isNil( translationData.translated_post ) && ! isNil( translationData.translated_post.id ) ) {
					translatedPosts.set( lang, translationData.translated_post.id );
				}
			}
		);
	}
	return translatedPosts;
}

/**
 * Gets synchronized posts.
 *
 * @param {Object.<string, boolean>} pll_sync_post The synchronized posts object with language codes as keys and boolean values to say if the post is synchronized or not.
 * @returns {Map}
 */
function getSynchronizedPosts( pll_sync_post ){
	let synchronizedPosts = new Map( Object.entries( [] ) );
	if ( ! isUndefined( pll_sync_post ) ) {
		synchronizedPosts = new Map( Object.entries( pll_sync_post ) );
	}
	return synchronizedPosts;
}

/**
 * Gets translations table.
 *
 * @param {Object.<string, Object>} translationsTableDatas The translations table data object with language codes as keys and data object as values.
 * @returns {Map}
 */
function getTranslationsTable( translationsTableDatas ){
	let translationsTable = new Map( Object.entries( [] ) );
	// get translations table datas from post
	if ( ! isUndefined( translationsTableDatas ) ) {
		// Build translations table map with language slug as key
		translationsTable = new Map( Object.entries( translationsTableDatas ) );
	}
	return translationsTable;
}

/**
 * Checks if the given request is for saving.
 *
 * @param {Object} options The initial request.
 * @returns {Boolean} True if the request is for saving.
 */
function isSaveRequest( options ){
	// If data is defined we are in a PUT or POST request method otherwise a GET request method
	// Test options.method property isn't efficient because most of REST request which use fetch API doesn't pass this property.
	// So, test options.data is necessary to know if the REST request is to save datas.
	// However test if options.data is undefined isn't sufficient because some REST request pass a null value as the ServerSideRender Gutenberg component.
	if ( ! isNil( options.data ) ) {
		return true;
	} else {
		return false;
	}
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
 * @returns {boolean} True if the request concerns the current post.
 */
function isCurrentPostRequest( options ){
	// Saving translation data is needed only for all post types.
	// It's done by verifying options.path matches with one of baseURL of all post types
	// and compare current post id with this sent in the request.

	// List of post type baseURLs.
	const postTypeURLs = map( select( 'core' ).getEntitiesByKind( 'postType' ), property( 'baseURL' ) );

	// Id from the post currently edited.
	const postId = select( 'core/editor' ).getCurrentPostId();

	// Id from the REST request.
	// options.data never isNil here because it's already verified before in isSaveRequest() function.
	const id = options.data.id;

	// Return true
	// if REST request baseURL matches with one of the known post type baseURLs
	// and the id from the post currently edited corresponds on the id passed to the REST request
	// Return false otherwise
	return -1 !== postTypeURLs.findIndex(
		function ( element ) {
			return new RegExp( `${ escapeRegExp( element ) }` ).test( options.path );
		}
	) && postId === id;
}

/**
 * Checks if the given REST request is for the creation of a new template part translation.
 *
 * @param {Object} options The initial request.
 * @returns {Boolean} True if the request concerns a template part translation creation.
 */
function isTemplatePartTranslationCreationRequest( options ) {
	return 'POST' === options.method
		&&  options.path.match( /^\/wp\/v2\/template-parts(?:\/|\?|$)/ )
		&& ! isNil( options.data.from_post )
		&& ! isNil( options.data.lang );
}

/**
 * Checks if the given REST request is for the creation of a new template part.
 *
 * @param {Object} options The initial request.
 * @returns {Boolean} True if the request concerns a template part creation.
 */
function isNewTemplatePartCreationRequest( options ) {
	return 'POST' === options.method
		&&  options.path.match( /^\/wp\/v2\/template-parts(?:\/|\?|$)/ )
		&& isNil( options.data.from_post )
		&& isNil( options.data.lang );
}

/**
 * Adds language as query string parameter to the given request.
 *
 * @param {Object} options         The initial request.
 * @param {string} currentLanguage The language code to add to the request.
 */
function addLanguageToRequest( options, currentLanguage ){
	const hasLangArg= (0,external_this_wp_url_.hasQueryArg)( options.path, 'lang' );
	const filterLang = (0,external_lodash_.isUndefined)( options.filterLang ) || options.filterLang;
	if ( filterLang && ! hasLangArg ) {
		options.path = (0,external_this_wp_url_.addQueryArgs)(
			options.path,
			{
				lang: currentLanguage
			}
		);
	}
}

/**
 * Adds `include_untranslated` parameter to the request.
 *
 * @param {Object} options The initial request.
 * @returns {void}
 */
function addIncludeUntranslatedParam( options ) {
	options.path = addQueryArgs(
		options.path,
		{
			include_untranslated: true
		}
	);
}

/**
 * Use addIncludeUntranslatedParam if the given page is a template part page.
 * Or if the template editing mode is enabled inside post editing.
 *
 * @param {Object} options The initial request.
 * @returns {void}
 */
function maybeRequireIncludeUntranslatedTemplate( options ) {
	const params = ( new URL( document.location ) ).searchParams;
	const postType = params.get( 'postType' );
	const postId = params.get( 'postId' );
	const isEditingTemplate = select( MODULE_POST_EDITOR_KEY )?.isEditingTemplate();
	if ( ( "wp_template_part" === postType && ! isNil( postId ) ) || isEditingTemplate ) {
		addIncludeUntranslatedParam( options );
	}
}

/**
 * Returns true if the given post is a template part, false otherwise.
 *
 * @param {Object} post A post object.
 * @returns {boolean} Whether it is a template part or not.
 */
function isTemplatePart( post ) {
	return 'wp_template_part' === post.type;
}

/**
 * Returns the current post type considering the Site Editor or Post Editor.
 *
 * @returns {string} The current post type.
 */
function getCurrentPostType() {
	if ( isSiteBlockEditor() ) {
		return select( MODULE_SITE_EDITOR_KEY ).getEditedPostType();
	}

	return select( MODULE_CORE_EDITOR_KEY ).getCurrentPostType();
}

/**
 * Returns a regular expression ready to use to perform search and replace.
 *
 * @returns {RegExp} The regular expression.
 */
function getLangSlugRegex() {
	let languageCheckPattern = TEMPLATE_PART_SLUG_CHECK_LANGUAGE_PATTERN;
	const languages = select( MODULE_KEY ).getLanguages();
	const languageSlugs = Array.from( languages.keys() );
	if ( ! isEmpty( languageSlugs ) ) {
		languageCheckPattern = languageSlugs.join( '|' );
	}

	return new RegExp( `${TEMPLATE_PART_SLUG_SEPARATOR}(?:${languageCheckPattern})$` );
}

;// ./modules/block-editor/js/sidebar/store/index.js
/**
 * WordPress Dependencies
 *
 * @package Polylang-Pro
 */




/**
 * Internal dependencies
 */



const actions = {
	setLanguages( languages ) {
		return {
			type: 'SET_LANGUAGES',
			languages
		};
	},
	setCurrentUser( currentUser, save = false ) {
		return {
			type: 'SET_CURRENT_USER',
			currentUser,
			save
		};
	},
	setFromPost( fromPost ) {
		return {
			type: 'SET_FROM_POST',
			fromPost,
		};
	},
	fetchFromAPI( options ) {
		return {
			type: 'FETCH_FROM_API',
			options,
		};
	}
};

const store = (0,external_this_wp_data_.createReduxStore)(
	settings_MODULE_KEY,
	{
		reducer( state = DEFAULT_STATE, action ) {
			switch ( action.type ) {
				case 'SET_LANGUAGES':
					return {
						...state,
						languages: action.languages
					};
				case 'SET_CURRENT_USER':
					if ( action.save ) {
						updateCurrentUser( action.currentUser ).then(
							currentUser => {
								action.currentUser = currentUser;
								return {
									...state,
									currentUser: action.currentUser
								};
						} );
					} else {
						return {
							...state,
							currentUser: action.currentUser
						}
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
			getLanguages( state ){
				return state.languages;
			},
			getCurrentUser( state ){
				return state.currentUser;
			},
			getFromPost( state ){
				return state.fromPost;
			}
		},
		actions,
		controls: {
			FETCH_FROM_API( action ) {
				return external_this_wp_apiFetch_default()( { ...action.options } );
			},
		},
		resolvers: {
			* getLanguages(){
				const path = '/pll/v1/languages';
				const languages = yield actions.fetchFromAPI( { path, filterLang: false } );
				return actions.setLanguages( convertArrayToMap( languages, 'slug' ) );
			},
			* getCurrentUser() {
				const path = '/wp/v2/users/me';
				const currentUser = yield actions.fetchFromAPI( { path, filterLang: true } );
				return actions.setCurrentUser( currentUser );
			}
		}
	}
);

(0,external_this_wp_data_.register)( store );

/**
 * Save current user when it is wondered.
 *
 * @param {object} currentUser
 * @returns {object} The current user updated.
 */
function updateCurrentUser( currentUser ) {
	return Promise.resolve(
		external_this_wp_apiFetch_default()(
			{
				path: '/wp/v2/users/me',
				data: currentUser,
				method: 'POST'
			}
		)
	);
}

;// ./modules/block-editor/js/middleware/filter-path-middleware.js
/**
 * @package Polylang Pro
 */

/**
 * Filters requests for translatable entities.
 * This logic is shared accross all Polylang plugins.
 *
 * @since 3.5
 *
 * @param {APIFetchOptions} options
 * @param {Array} filteredRoutes
 * @param {CallableFunction} filter
 * @returns {APIFetchOptions}
 */
const filterPathMiddleware = ( options, filteredRoutes, filter ) => {
	const cleanPath = options.path.split( '?' )[0].replace(/^\/+|\/+$/g, ''); // Get path without query parameters and trim '/'.

	return Object.values( filteredRoutes ).find( ( path ) => cleanPath === path ) ? filter( options ) : options;
}

/* harmony default export */ const filter_path_middleware = (filterPathMiddleware);

;// ./modules/block-editor/js/widget-editor-plugin.js
/**
 * WordPress dependencies
 *
 * @package Polylang-Pro
 */

/**
 * WordPress dependencies.
 */


/**
 * Internal dependencies.
 */
 // Store used for Polylang block attribute.



/*
 * Specific scripts with block editor
 */
external_this_wp_apiFetch_default().use(
	( options, next ) => {
		/*
		 * If options.url is defined, this is not a REST request but a direct call to post.php for legacy metaboxes.
		 * If `filteredRoutes` is not defined, return early.
		 */
		if ( 'undefined' !== typeof options.url || 'undefined' === typeof pllFilteredRoutes ) {
			return next( options );
		}

		return next( filter_path_middleware( options, pllFilteredRoutes, addParametersToRequest ) );
	}
);

/**
 * Adds parameters according to the context of the request.
 *
 * @since 3.5
 *
 * @param {APIFetchOptions} options
 * @returns {APIFetchOptions}
 */
const addParametersToRequest = ( options ) => {
	addLanguageToRequest( options, pllDefaultLanguage );

	return options;
}

})();

this["polylang-pro"] = __webpack_exports__;
/******/ })()
;