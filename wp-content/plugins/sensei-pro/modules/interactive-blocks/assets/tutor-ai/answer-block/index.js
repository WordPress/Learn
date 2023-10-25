/**
 * External dependencies
 */
import { ReactComponent as icon } from 'sensei/assets/icons/question.svg';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './answer-edit';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const tutorAiBlock = {
	...metadata,
	title: __( 'AI Answer', 'sensei-pro' ),
	icon,
	parent: [ 'sensei-pro/tutor-ai' ],
	description: __( 'Answer to the Tutor AI question.', 'sensei-pro' ),
	keywords: [
		__( 'tutor', 'sensei-pro' ),
		__( 'ai', 'sensei-pro' ),
		__( 'question', 'sensei-pro' ),
		__( 'answer', 'sensei-pro' ),
	],
	edit,
	save: ( { blockProps } ) => {
		return <div { ...blockProps }></div>;
	},
};

export default tutorAiBlock;
