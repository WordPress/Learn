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
	} );
} )( jQuery );
