/**
 * File filters.js.
 *
 * Handles ui component that add and remove filters
 */
( function ( $ ) {
	$( document ).ready( function() {
		$( '.filter-group-select' ).select2( {
			allowClear: true,
		} );

		// Try to force screen readers to use select element and hide select2
		if ( $( '.filter-group-select' ).hasClass( 'select2-hidden-accessible' ) ) {
			$( '.selection' ).attr( { 'aria-hidden': 'true', 'tabindex': '-1' } );
			$( '.filter-group-select' ).removeAttr( 'aria-hidden' ).attr( 'tabindex', '0' );
		}
	} );
} )( jQuery );
