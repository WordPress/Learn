document.addEventListener( 'DOMContentLoaded', function () {
	const facilitatorNotes = document.querySelector( '.wporg-learn-lesson-facilitator-notes-label' );
	const headerInfo = document.querySelector( '.sensei-course-theme__header__info' );

	if ( facilitatorNotes && headerInfo ) {
		headerInfo.insertBefore( facilitatorNotes, headerInfo.lastChild );
	}
} );
