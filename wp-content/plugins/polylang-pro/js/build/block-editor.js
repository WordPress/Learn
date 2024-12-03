/******/ "use strict";

;// ./vendor/wpsyntex/polylang/js/src/lib/confirmation-modal.js
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

;// ./vendor/wpsyntex/polylang/js/src/lib/metabox-autocomplete.js
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

;// ./vendor/wpsyntex/polylang/js/src/lib/filter-path-middleware.js
/**
 * @package Polylang
 */

/**
 * Filters requests for translatable entities.
 * This logic is shared across all Polylang plugins.
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

;// ./vendor/wpsyntex/polylang/js/src/block-editor.js
/**
 * @package Polylang
 */







/**
 * Filter REST API requests to add the language in the request
 *
 * @since 2.5
 */
wp.apiFetch.use(
	function ( options, next ) {
		/*
		 * If options.url is defined, this is not a REST request but a direct call to post.php for legacy metaboxes.
		 * If `filteredRoutes` is not defined, return early.
		 */
		if ( 'undefined' !== typeof options.url || 'undefined' === typeof pllFilteredRoutes ) {
			return next( options );
		}

		return next( filter_path_middleware( options, pllFilteredRoutes, addLanguageParameter ) );
	}
);

/**
 * Gets the language of the currently edited post, fallback to default language if none is found.
 *
 * @since 2.5
 *
 * @return {Element.value}
 */
function getCurrentLanguage() {
	const lang = document.querySelector( '[name=post_lang_choice]' );

	if ( null === lang ) {
		return pllDefaultLanguage;
	}

	return lang.value;
}

/**
 * Adds language parameter according to the current one (query string for GET, body for PUT and POST).
 *
 * @since 3.5
 *
 * @param {APIFetchOptions} options
 * @returns {APIFetchOptions}
 */
function addLanguageParameter( options ) {
	if ( 'undefined' === typeof options.data || null === options.data ) {
		// GET
		options.path += ( ( options.path.indexOf( '?' ) >= 0 ) ? '&lang=' : '?lang=' ) + getCurrentLanguage();
	} else {
		// PUT, POST
		options.data.lang = getCurrentLanguage();
	}

	return options;
}

/**
 * Handles internals of the metabox:
 * Language select, autocomplete input field.
 *
 * @since 1.5
 *
 * Save post after lang choice is done and redirect to the same page for refreshing all the data.
 *
 * @since 2.5
 *
 * Link post saving after refreshing the metabox.
 *
 * @since 3.0
 */
jQuery(
	function ( $ ) {
		// Initialize current language to be able to compare if it changes.
		initializeLanguageOldValue();


		// Ajax for changing the post's language in the languages metabox
		$( '.post_lang_choice' ).on(
			'change',
			function ( event ) {
				const { select, dispatch, subscribe } = wp.data;
				const emptyPost                       = isEmptyPost();
				const { addQueryArgs }                = wp.url;

				// Initialize the confirmation dialog box.
				const confirmationModal            = initializeConfirmationModal();
				const { dialogContainer : dialog } = confirmationModal;
				let { dialogResult }               = confirmationModal;
				const selectedOption               = event.target; // The selected option in the dropdown list.

				// Specific case for empty posts.
				// Place at the beginning because window.location change triggers automatically page reloading.
				if ( location.pathname.match( /post-new.php/gi ) && emptyPost ) {
					reloadPageForEmptyPost( selectedOption.value );
				}

				// Otherwise send an ajax request to refresh the legacy metabox and set the post language with the new language.
				// It needs a confirmation of the user before changing the language.
				// Need to wait the ajax response before triggering the block editor post save action.
				if ( $( this ).data( 'old-value' ) !== selectedOption.value && ! emptyPost ) {
					dialog.dialog( 'open' );
				} else {
					// Update the old language with the new one to be able to compare it in the next change.
					// Because the page isn't reloaded in this case.
					initializeLanguageOldValue();
					dialogResult = Promise.resolve();
				}

				dialogResult.then(
					() => {
						let data = { // phpcs:ignore PEAR.Functions.FunctionCallSignature.Indent
							action:     'post_lang_choice',
							lang:       selectedOption.value,
							post_type:  $( '#post_type' ).val(),
							post_id:    $( '#post_ID' ).val(),
							_pll_nonce: $( '#_pll_nonce' ).val()
						}

						// Update post language in database as soon as possible.
						// Because, in addition of the block editor save process, the legacy metabox uses a post.php process to update the language and is too late compared to the page reload.
						$.post(
							ajaxurl,
							data,
							function () {
								blockEditorSavePostAndReloadPage();
							}
						);
					},
					() => {} // Do nothing when promise is rejected by clicking the Cancel dialog button.
				);

				function isEmptyPost() {
					const editor = select( 'core/editor' );

					return ! editor.getEditedPostAttribute( 'title' )?.trim() && ! editor.getEditedPostContent() && ! editor.getEditedPostAttribute( 'excerpt' )?.trim();
				}

				/**
				 * Reload the block editor page for empty posts.
				 *
				 * @param {string} lang The target language code.
				 */
				function reloadPageForEmptyPost( lang ) {
					// Change the new_lang parameter with the new language value for reloading the page
					// WPCS location.search is never written in the page, just used to reload page with the right value of new_lang
					// new_lang input is controlled server side in PHP. The value come from the dropdown list of language returned and escaped server side.
					// Notice that window.location changing triggers automatically page reloading.
					if ( -1 != location.search.indexOf( 'new_lang' ) ) {
						// use regexp non capturing group to replace new_lang parameter no matter where it is and capture other parameters which can be behind it
						window.location.search = window.location.search.replace( /(?:new_lang=[^&]*)(&)?(.*)/, 'new_lang=' + lang + '$1$2' ); // phpcs:ignore WordPressVIPMinimum.JS.Window.location, WordPressVIPMinimum.JS.Window.VarAssignment
					} else {
						window.location.search = window.location.search + ( ( -1 != window.location.search.indexOf( '?' ) ) ? '&' : '?' ) + 'new_lang=' + lang; // phpcs:ignore WordPressVIPMinimum.JS.Window.location, WordPressVIPMinimum.JS.Window.VarAssignment
					}
				};

				/**
				 * Triggers block editor post save and reload the block editor page when everything is ok.
				 */
				function blockEditorSavePostAndReloadPage() {

					let unsubscribe    = null;
					const previousPost = select( 'core/editor').getCurrentPost();

					// Listen if the savePost is completely done by subscribing to its events.
					const savePostIsDone = new Promise(
						function ( resolve, reject ) {
							unsubscribe = subscribe(
								function () {
									const post                 = select( 'core/editor').getCurrentPost();
									const { id, status, type } = post;
									const error                = select( 'core' )
										.getLastEntitySaveError(
											'postType',
											type,
											id
										);

									if ( error ) {
										reject();
									}

									if ( previousPost.modified !== post.modified ) {

										if ( location.pathname.match( /post-new.php/gi ) && status !== 'auto-draft' && id ) {
											window.history.replaceState(
												{ id },
												'Post ' + id,
												addQueryArgs( 'post.php', { post: id, action: 'edit' } )
											);
										}
										resolve();
									}
								}
							);
						}
					);

					// Triggers the post save.
					dispatch( 'core/editor' ).savePost();

					// Process
					savePostIsDone.then(
						function () {
							// If the post is well saved, we can reload the page
							window.location.reload();
						},
						function () {
							// If the post save failed
							unsubscribe();
						}
					).catch(
						function () {
							// If an exception is thrown
							unsubscribe();
						}
					);
				};
			}
		);

		initMetaboxAutoComplete();
	}
);

