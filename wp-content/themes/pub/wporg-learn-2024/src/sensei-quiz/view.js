/* global lesson */

import { __ } from '@wordpress/i18n';

function init() {
	const actions = document.querySelector( '.sensei-quiz-actions' );
	const button = actions.querySelector( '.sensei-quiz-actions .wp-element-button.sensei-course-theme__button' );

	button.innerText = __( 'Back to lesson', 'wporg-learn' );
	button.removeAttribute( 'disabled' );
	button.addEventListener( 'click', function () {
		window.location.href = lesson ? lesson.link : '';
	} );
}

document.addEventListener( 'DOMContentLoaded', init );
