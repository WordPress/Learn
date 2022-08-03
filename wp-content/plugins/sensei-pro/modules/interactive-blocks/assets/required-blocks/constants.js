/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * The map of block type names and their respective counter labels.
 *
 * @member {Object.<string, string>}
 */
export const blockTypeLabels = {
	'sensei-pro/flashcard': __( 'Flashcards', 'sensei-pro' ),
	'sensei-pro/image-hotspots': __( 'Image Hotspots', 'sensei-pro' ),
	'sensei-pro/task-list': __( 'Task Lists', 'sensei-pro' ),
	'sensei-pro/question': __( 'Questions', 'sensei-pro' ),
	'sensei/video': __( 'Videos', 'sensei-pro' ),
};
