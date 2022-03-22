import { addFilter } from '@wordpress/hooks';
import { compose } from '@wordpress/compose';
import withQuizTimerSettings from './with-quiz-timer-settings';

const extendQuizSettings = ( settings ) => {
	if ( 'sensei-lms/quiz' !== settings.name ) {
		return settings;
	}

	return {
		...settings,
		edit: compose( withQuizTimerSettings )( settings.edit ),
	};
};

addFilter(
	'blocks.registerBlockType',
	'sensei-lms/with-inspector-controls',
	extendQuizSettings
);
