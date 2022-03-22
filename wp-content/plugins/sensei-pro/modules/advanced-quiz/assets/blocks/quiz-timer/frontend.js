import domReady from '@wordpress/dom-ready';
import { render } from 'react-dom';
import { Timer } from './components/Timer';

domReady( () => {
	const timeLeft = parseInt( window.sensei_quiz_timer_params.time_left, 10 );

	const submitQuiz = () => {
		const submitButton = document.querySelector(
			'button[name="quiz_complete"]'
		);
		submitButton?.click();
	};

	const timerElement = document.getElementById( 'sensei-quiz-timer' );
	const isCourseTheme = window.sensei_quiz_timer_params.is_course_theme;
	if ( isCourseTheme ) {
		const quizThemeHeaderRight = document.querySelector(
			'.sensei-course-theme__quiz__header__right'
		);
		if ( quizThemeHeaderRight ) {
			timerElement.remove();
			quizThemeHeaderRight.append( timerElement );
			quizThemeHeaderRight.classList.add(
				'sensei-course-theme__quiz__header__right--with-timer'
			);
		}
	}

	if ( timerElement ) {
		let deadlineTimestamp = 0;

		if ( timeLeft ) {
			deadlineTimestamp = timeLeft * 1000 + Date.now();
			setTimeout( submitQuiz, timeLeft * 1000 );
		}

		render(
			<Timer
				time={ window.sensei_quiz_timer_params.time }
				deadline={ deadlineTimestamp }
				isPreviewMode={
					! window.sensei_quiz_timer_params.is_not_started
				}
				isCourseTheme={
					window.sensei_quiz_timer_params.is_course_theme
				}
			/>,
			timerElement
		);
	}
} );
