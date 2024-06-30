/* global wporgCourseOutlineData */

document.addEventListener( 'DOMContentLoaded', function () {
	const lessonData = wporgCourseOutlineData;

	lessonData.forEach( function ( lesson ) {
		const title = lesson.title;
		const icon = lesson.icon;

		const lessonLinks = document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson' );

		lessonLinks.forEach( function ( link ) {
			const span = link.querySelector( 'span' );

			if ( span && span.textContent.trim() === title ) {
				const statusIcon = link.querySelector( '.wp-block-sensei-lms-course-outline-lesson__status' );
				if ( statusIcon ) {
					statusIcon.outerHTML = icon;
				}
			}
		} );
	} );
} );
