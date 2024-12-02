/**
 * Bulk translate
 *
 * @package Polylang-Pro
 */

jQuery(
	function ( $ ) {
		var t = this;

		$( '.editinline' ).on(
			'click',
			function () {
				$( '#pll-translate' ).find( '.cancel' ).trigger( 'click' ); // Close the form on quick edit
			}
		);

		$( '#doaction, #doaction2' ).on(
			'click',
			function ( e ) {
				t.whichBulkButtonId = $( this ).attr( 'id' );
				var n = t.whichBulkButtonId.substr( 2 );

				if ( 'pll_translate' === $( 'select[name="' + n + '"]' ).val() ) {
					e.preventDefault();

					if ( typeof inlineEditPost !== 'undefined' ) { // Not available for media.
						inlineEditPost.revert(); // Close Bulk edit and Quick edit if open.
					}

					$( '#pll-translate td' ).attr( 'colspan', $( 'th:visible, td:visible', '.widefat:first thead' ).length );
					// The hidden tr allows to keep the background color.
					// HTML prepended is hardcoded. So prepend is safe and as no need to be escaped.
					$( 'table.widefat tbody' ).prepend( $( '#pll-translate' ) ).prepend( '<tr class="hidden"></tr>' ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.prepend
				} else {
					$( '#pll-translate' ).find( '.cancel' ).trigger( 'click' );
				}
			}
		);

		// Cancel
		$( '#pll-translate' ).on(
			'click',
			'.cancel',
			function () {
				// Close the form on any other bulk action
				$( '#pll-translate' ).siblings( '.hidden' ).remove();
				// #pll-translate is built and come from server side and is well escaped when necessary
				$( '#pll-bulk-translate' ).append( $( '#pll-translate' ) ); //phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.append

				// Move focus back to the Bulk Action button that was activated.
				$( '#' + t.whichBulkButtonId ).trigger( 'focus' );
			}
		);

		// Act when pressing enter or esc
		$( '#pll-translate' ).on(
			'keydown',
			function ( event ) {
				if ( 'Enter' === event.key && ! $( event.target ).hasClass( 'cancel' ) ) {
					event.preventDefault();
					$( this ).find( 'input[type=submit]' ).trigger( 'click' );
				}
				if ( 'Escape' === event.key ) {
					event.preventDefault();
					$( this ).find( '.cancel' ).trigger( 'click' );
				}
			}
		);

		// Clean DOM in case of file download
		$( '#posts-filter' ).on(
			'submit',
			function () {
				$( '.settings-error' ).remove();
				setTimeout(
					function () {
						$( 'input[type=checkbox]:checked' ).attr( 'checked', false );
						$( '#pll-translate' ).find( '.cancel' ).trigger( 'click' );
					},
					500
				);
			}
		);
	}
);
