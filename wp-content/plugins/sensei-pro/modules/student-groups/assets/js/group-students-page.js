/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

domReady( () => {
	// eslint-disable-next-line no-undef
	const $ = jQuery;

	const moreLink = $( '.sensei-group-students__enrolled-courses-more-link' );
	moreLink.on( 'click', function ( event ) {
		event.preventDefault();
		event.stopPropagation();

		$( event.target ).addClass( 'hidden' ).prev().removeClass( 'hidden' );
	} );
} );
