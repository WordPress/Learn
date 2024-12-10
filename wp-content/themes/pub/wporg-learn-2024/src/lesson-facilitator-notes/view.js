document.addEventListener( 'DOMContentLoaded', function () {
	const facilitatorNotes = document.querySelector( '.wporg-learn-lesson-facilitator-notes-label' );
	const headerInfos = document.querySelectorAll( '.sensei-course-theme-course-progress' );

	if ( facilitatorNotes && headerInfos.length > 0 ) {
		headerInfos.forEach( ( headerInfo ) => {
			headerInfo.insertAdjacentElement( 'afterend', facilitatorNotes.cloneNode( true ) );
		} );
	}
} );
