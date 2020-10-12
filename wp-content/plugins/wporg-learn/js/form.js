const jQuery = window.jQuery || {};

( ( $ ) => {
	const checkOther = document.querySelectorAll( '.checkbox-and-text' );

	Array.from( checkOther ).forEach( function ( container ) {
		const checkbox = container.querySelector( 'input[type="checkbox"]' ),
			text = container.querySelector( 'input[type="text"]' );

		text.addEventListener(
			'input',
			( event ) => ( checkbox.checked = !! event.target.value )
		);

		checkbox.addEventListener( 'change', ( event ) => {
			if ( event.target.checked ) {
				text.focus();
			} else {
				text.value = '';
			}
		} );
	} );

	$( '.do-select2' ).select2( {
		dropdownParent: $( '.wporg-learn-workshop-application-form' ),
	} );
} )( jQuery );
