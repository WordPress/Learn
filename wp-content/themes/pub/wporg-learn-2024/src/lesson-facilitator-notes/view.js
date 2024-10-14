function init() {
	const backdrop = document.querySelector( '.wporg-learn-facilitator-notes-backdrop' );
	const closeButton = document.querySelector( '.wporg-learn-facilitator-notes-close' );
	const toggle = document.getElementById( 'wporg-learn-facilitator-notes-toggle' );

	backdrop.addEventListener( 'click', function () {
		toggle.checked = false;
	} );

	closeButton.addEventListener( 'click', function () {
		toggle.checked = false;
	} );
}

document.addEventListener( 'DOMContentLoaded', init );
