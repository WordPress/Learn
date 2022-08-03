/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import playingGuitarImg from './assets/playing-guitar.jpg';

/**
 * The example configuration for the Image Hotspots.
 *
 * @member {Object}
 */
export const example = {
	attributes: {
		image: {
			url: playingGuitarImg,
			alt: __( 'Playing a Guitar', 'sensei-pro' ),
		},
		className: 'is-example',
	},
	innerBlocks: [
		{
			name: 'sensei-pro/image-hotspots-hotspot',
			attributes: {
				x: 78.07551766138855,
				y: 53.09658583093921,
			},
		},
		{
			name: 'sensei-pro/image-hotspots-hotspot',
			attributes: {
				x: 23.26431181485993,
				y: 43.683814657342026,
			},
		},
		{
			name: 'sensei-pro/image-hotspots-hotspot',
			attributes: {
				x: 36.90621193666261,
				y: 17.156914077204476,
			},
		},
		{
			name: 'sensei-pro/image-hotspots-hotspot',
			attributes: {
				x: 87.33252131546894,
				y: 70.21071523747958,
			},
		},
		{
			name: 'sensei-pro/image-hotspots-hotspot',
			attributes: {
				x: 46.041412911084045,
				y: 46.250934068323076,
			},
			innerBlocks: [
				{
					name: 'core/heading',
					attributes: {
						content: __( 'Fretboard', 'sensei-pro' ),
						level: 4,
					},
				},
				{
					name: 'core/paragraph',
					attributes: {
						content: __(
							'The fretboard is also known as the neck. On a guitar fretboard, it is divided up into many …',
							'sensei-pro'
						),
						dropCap: false,
					},
				},
			],
		},
	],
};
