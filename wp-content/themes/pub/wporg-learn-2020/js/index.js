/**
 * File index.js.
 *
 * Adds progressive enhancements to wporg-learn theme
 */
( function() {

	/**
	* Binds a click event to the list of workshop images used in templates-parts/component-video-grid
	*/
	function bindWorkshopImageNavigation() {
		var container = document.getElementById( 'workshop-grid' );

		if( ! container ) return;
	
		var items = container.querySelectorAll( 'li' );
	
		for( var i = 0; i < items.length; i++ ) {
			var item = items[ 0 ];
			var img = item.querySelector( 'img' );
			var link = item.querySelector( 'a' );
			
			img.addEventListener( 'click', function () {
				window.location.href = link.href;
			} )
		}
	}

	/**
	* Binds a click event to the featured workshop image used in templates-parts/component-featured-workshop.php
	*/
	function bindFeatureWorkshopImageNavigation() {
		var container = document.getElementById( 'featured-workshop' );

		if( ! container ) return;

		var img = container.querySelector( 'img' );
		var link = container.querySelector( 'a' );

		img.addEventListener( 'click', function () {
			window.location.href = link.href;
		} )
	}

	bindWorkshopImageNavigation();
	bindFeatureWorkshopImageNavigation();

} )();
