/* global wpCookies */
( function( window, $, wpCookies ) {

	'use strict';

	var localeNotice = window.WPOrgLearnLocaleNotice || {},
		app;

	app = $.extend( localeNotice, {
		$notice: $(),

		init: function() {
			app.$notice   = $( '.wporg-learn-locale-notice' );

			app.$notice.on( 'click', '.wporg-learn-locale-notice-dismiss', function( event ) {
				event.preventDefault();
				app.dismissNotice();
			} );
		},

		dismissNotice: function() {
			app.$notice.fadeTo( 100, 0, function() {
				app.$notice.slideUp( 100, function() {
					app.$notice.remove();
				});
			});

			wpCookies.set(
				'wporg-learn-locale-notice-dismissed',
				true,
				app.cookie.expires,
				app.cookie.cpath,
				app.cookie.domain,
				app.cookie.secure
			);
		}
	} );

	$( document ).ready( function() {
		app.init();
	} );

} )( window, jQuery, wpCookies );
