/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * The example configuration for the Flashcard.
 *
 * @member {Object}
 */
export const example = {
	innerBlocks: [
		{
			name: 'core/cover',
			attributes: {
				customOverlayColor: '#43af99',
				minHeight: 300,
				minHeightUnit: 'px',
			},
			innerBlocks: [
				{
					name: 'core/paragraph',
					attributes: {
						align: 'center',
						content: __( 'What is a metronome?', 'sensei-pro' ),
						dropCap: false,
						placeholder: __(
							'Add flash card question',
							'sensei-pro'
						),
						textColor: 'white',
						fontSize: 'large',
					},
				},
			],
		},
	],
};
