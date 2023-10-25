/**
 * External dependencies
 */
import { ReactComponent as icon } from 'sensei/assets/icons/question.svg';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './student-text-edit';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const tutorAiBlock = {
	...metadata,
	title: __( 'Student Answer', 'sensei-pro' ),
	icon,
	parent: [ 'sensei-pro/tutor-ai' ],
	description: __(
		"Student's reply to the Tutor AI question.",
		'sensei-pro'
	),
	keywords: [
		__( 'tutor', 'sensei-pro' ),
		__( 'ai', 'sensei-pro' ),
		__( 'question', 'sensei-pro' ),
		__( 'answer', 'sensei-pro' ),
	],
	edit,
	save: ( { blockProps, children } ) => {
		return <div { ...blockProps }>{ children }</div>;
	},
};

export default tutorAiBlock;
