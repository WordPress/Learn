/**
 * File filters.js.
 *
 * Handles ui component that add and remove filters
 */
( function ( $ ) {
	var filterContainer = document.querySelector( '.js-filter-drawer' );
	var toggleFilterButton = document.querySelector(
		'.js-filter-drawer-toggle'
	);

	// Check to see we have filter functionality.
	if ( ! filterContainer || ! toggleFilterButton ) {
		return;
	}

	toggleFilterButton.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		filterContainer.classList.toggle( 'show-filters' );
	} );

	$( document ).ready( function() {
		$( '.filter-group-select' ).select2( {
			allowClear: true,
		} );
	} );
} )( jQuery );
