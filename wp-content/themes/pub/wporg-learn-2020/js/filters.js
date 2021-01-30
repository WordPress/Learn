/**
 * File filters.js.
 *
 * Handles ui component that add and remove filters
 */
( function ( $ ) {
	$( document ).ready( function() {
		var $filters = $( '.filter-group-select' ).select2( {
			allowClear: true,
		} );

		// Try to force screen readers to use select element and hide select2.
		$filters.each( function() {
			if ( $( this ).hasClass( 'select2-hidden-accessible' ) ) {
				$( this ).siblings( '.select2-container' ).find( '.selection' ).attr( { 'aria-hidden': 'true', 'tabindex': '-1' } );
				$( this ).removeAttr( 'aria-hidden' ).attr( 'tabindex', '0' );
			}
		} );
	} );
} )( jQuery );
