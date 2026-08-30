document.addEventListener( 'DOMContentLoaded', function () {
	const facilitatorNotes = document.querySelector( '.wporg-learn-lesson-facilitator-notes-label' );
	const exitCourse = document.querySelector( '.wp-block-sensei-lms-exit-course' );
	const exitLesson = document.querySelector( '.wp-block-sensei-lms-exit-lesson' );
	const sidebarToggle = document.querySelector( '.wporg-learn-lesson-sidebar-toggle-wrapper' );

	// for desktop view
	if ( facilitatorNotes && exitCourse ) {
		exitCourse.insertAdjacentElement( 'beforebegin', facilitatorNotes.cloneNode( true ) );
	}

	// for mobile view
	if ( facilitatorNotes && sidebarToggle ) {
		sidebarToggle.insertAdjacentElement( 'beforebegin', facilitatorNotes.cloneNode( true ) );
	}

	// for standalone lesson
	if ( facilitatorNotes && exitLesson ) {
		exitLesson.insertAdjacentElement( 'beforebegin', facilitatorNotes.cloneNode( true ) );
	}

	facilitatorNotes.remove();
} );
