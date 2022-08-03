/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export const example = {
	attributes: {
		style: { color: { background: '#ffffff' } },
		className: 'is-example',
	},
	viewportWidth: 600,
	innerBlocks: [
		{
			name: 'sensei-pro/task-list-task',
			attributes: {
				text: __( 'Learn the meaning of the words.', 'sensei-pro' ),
				checked: true,
			},
		},
		{
			name: 'sensei-pro/task-list-task',
			attributes: {
				text: __(
					'Write down three sentences for each of the words.',
					'sensei-pro'
				),
				checked: true,
			},
		},
		{
			name: 'sensei-pro/task-list-task',
			attributes: {
				text:
					'Use the each of the words in a conversation with someone.',
				checked: true,
			},
		},
		{
			name: 'sensei-pro/task-list-task',
			attributes: {
				text: __(
					'Complete the flashcards for the words.',
					'sensei-pro'
				),
			},
		},
		{
			name: 'sensei-pro/task-list-task',
			attributes: {
				text: __(
					'Learn about the synonyms and antonyms of the words.',
					'sensei-pro'
				),
			},
		},
	],
};
