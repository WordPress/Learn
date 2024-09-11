/* global lesson */

import { __ } from '@wordpress/i18n';

function init() {
	// Quiz page actions.
	const actions = document.querySelector( '.sensei-quiz-actions' );
	if ( actions ) {
		const button = actions?.querySelector(
			'.sensei-quiz-actions .wp-element-button.sensei-course-theme__button'
		);

		if ( ! button ) {
			return;
		}

		if ( button.textContent.trim() === 'Pending teacher grade' ) {
			button.innerText = __( 'Back to lesson', 'wporg-learn' );
			button.removeAttribute( 'disabled' );
			button.addEventListener( 'click', function () {
				window.location.href = lesson ? lesson.link : '';
			} );
		}

		button.style.visibility = 'visible';
	}

	// Lesson page quiz notice.
	const noticeContent = document.querySelector( '.sensei-course-theme-lesson-quiz-notice__content' );
	const grade = noticeContent?.querySelector( '.sensei-course-theme-lesson-quiz-notice__grade' );
	if ( noticeContent && ! grade ) {
		const newParagraph = document.createElement( 'p' );
		const noticeText = noticeContent.querySelector( '.sensei-course-theme-lesson-quiz-notice__text' );

		noticeText.remove();
		newParagraph.textContent =
			'[TBD. Sentence conveying that user is waiting for the teacher to assign a grade]';
		newParagraph.classList.add( 'sensei-course-theme-lesson-quiz-notice__description' );
		noticeContent.insertAdjacentElement( 'afterend', newParagraph );
	}
}

document.addEventListener( 'DOMContentLoaded', init );
