document.addEventListener( 'DOMContentLoaded', function () {
	const facilitatorNotes = document.querySelector( '.wporg-learn-lesson-facilitator-notes-label' );
	const headerInfos = document.querySelectorAll( '.sensei-course-theme-course-progress' );
	const headerContent = document.querySelector( '.sensei-course-theme-header-content' );

	if ( facilitatorNotes && headerInfos.length > 0 ) {
		headerInfos.forEach( ( headerInfo ) => {
			headerInfo.insertAdjacentElement( 'afterend', facilitatorNotes.cloneNode( true ) );
		} );
	} else if ( facilitatorNotes && headerContent ) {
		headerContent.firstElementChild.insertAdjacentElement( 'afterend', facilitatorNotes.cloneNode( true ) );
	}

	facilitatorNotes.remove();
} );
