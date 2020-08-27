/**
 * File filters.js.
 *
 * Handles ui component that add and remove filters
 */
( function () {
	var filterContainer = document.querySelector( '.js-filter-drawer' );
	var toggleFilterButton = document.querySelector(
		'.js-filter-drawer-toggle'
	);

	// Check to see we have filter functionality.
	if ( ! filterContainer || ! toggleFilterButton ) {
		return;
	}

	var applyFiltersButton = document.querySelector(
		'.js-apply-filters-toggle'
	);
	var clearFiltersButton = document.querySelector(
		'.js-clear-filters-toggle'
	);
	var filterDrawerForm = document.querySelector( '.js-filter-drawer-form' );
	var items = filterDrawerForm.querySelectorAll( 'input[type="checkbox"]' );

	function clearCheckboxes() {
		for ( var i = 0; i < items.length; i++ ) {
			items[ i ].checked = false;
		}
	}

	function hasCheckedItems() {
		for ( var i = 0; i < items.length; i++ ) {
			if ( items[ i ].checked ) {
				return true;
			}
		}

		return false;
	}

	toggleFilterButton.addEventListener( 'click', function () {
		filterContainer.classList.toggle( 'show-filters' );
	} );

	// Listen to the form and check if they added/removed items
	filterDrawerForm.addEventListener( 'click', function () {
		applyFiltersButton.disabled = ! hasCheckedItems();
	} );

	clearFiltersButton.addEventListener( 'click', function () {
		clearCheckboxes();
	} );
} )();
