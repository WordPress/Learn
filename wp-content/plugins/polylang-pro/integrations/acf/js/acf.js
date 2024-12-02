/**
 * @package Polylang-Pro
 */

jQuery(
	function ( $ ) {
		/**
		 * Ajax for changing the post's language in the languages metabox.
		 */
		$( '.post_lang_choice' ).on(
			'change',
			function () {

				// Reloads the relationship fields
				if ( $( ".acf-field-relationship" ).length ) {
					acf.doAction( 'ready' );
				}

				var fields = new Array();

				$( '.acf-field-taxonomy' ).each(
					function () {
						var field = $( this ).attr( 'data-key' );
						fields.push( field );
					}
				);

				if ( 0 != fields.length ) {
					var data = {
						action:     'acf_post_lang_choice',
						lang:       $( this ).val(),
						fields:     fields,
						_pll_nonce: $( '#_pll_nonce' ).val()
					}

					$.post(
						ajaxurl,
						data,
						function (response) {
							// Target a non existing WP HTML id to avoid a conflict with WP ajax requests.
							var res = wpAjax.parseAjaxResponse( response, 'pll-ajax-response' );
							$.each(
								res.responses,
								function () {
									$el = $( '.acf-' + this.what )
									// Data come from ACF field and server side.
									$el.html( this.data ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
									acf.do_action( 'ready_field/type=' + $el.data( 'type' ), $el );
								}
							);
						}
					);
				}
			}
		);
	}
);
