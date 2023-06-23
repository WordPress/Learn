/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { select } from '@wordpress/data';

// Filter out the "Existing Question(s)" option for co-teachers.
addFilter( 'sensei-lms.Quiz.appender-controls', 'sensei-lms', ( controls ) => {
	if ( select( 'core/editor' ).getCurrentPost().is_coteacher ) {
		return controls.filter(
			( control ) => control.id !== 'existing-question'
		);
	}

	return controls;
} );
