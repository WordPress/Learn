import { addFilter } from '@wordpress/hooks';

function addQuizTimerSettings( settings, name ) {
	if ( name !== 'sensei-lms/quiz' ) {
		return settings;
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'sensei-lms/quiz-timer',
	addQuizTimerSettings
);
