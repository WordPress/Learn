/**
 * External dependencies
 */
import { ReactComponent as icon } from 'sensei/assets/icons/question.svg';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './question-edit';
import metadata from './block.json';

const questionBlock = {
	...metadata,
	title: __( 'Question', 'sensei-pro' ),
	icon,
	description: __(
		'Add interactive questions (multiple-choice, true/false, etc.) with feedback anywhere.',
		'sensei-pro'
	),
	keywords: [
		__( 'question', 'sensei-pro' ),
		__( 'quiz', 'sensei-pro' ),
		__( 'test', 'sensei-pro' ),
		__( 'course', 'sensei-pro' ),
		__( 'lesson', 'sensei-pro' ),
		__( 'Sensei', 'sensei-pro' ),
	],
	edit,
	save: ( { children, blockProps } ) => {
		return <div { ...blockProps }>{ children }</div>;
	},
};

export default questionBlock;
