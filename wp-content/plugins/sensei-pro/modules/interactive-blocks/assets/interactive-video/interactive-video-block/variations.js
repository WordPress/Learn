/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { video } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { embedYouTubeIcon, embedVimeoIcon, embedVideoIcon } from './icons';
import { ReactComponent as interactiveVideoIcon } from '../../icons/interactive-video-block.svg';
import timelineBlockMeta from '../timeline-block';

/**
 * Shared options.
 */
const scope = [ 'block' ];
const isActive = ( blockAttributes, variationAttributes ) =>
	blockAttributes.videoType === variationAttributes.videoType;
const timelineBlock = [ timelineBlockMeta.name ];

const videoFileVariation = {
	name: 'video-file',
	title: __( 'Interactive Video' ),
	attributes: { videoType: 'video-file' },
	innerBlocks: [ [ 'core/video' ], timelineBlock ],
	isActive,
	scope,
	icon: video,
};

/**
 * Interactive video block variations.
 */
const variations = [
	// Default variation based on `videoFileVariation` – so we can display the correct default icon.
	{
		...videoFileVariation,
		name: 'default',
		isActive: () => false, // Never active since the active one must be `video-file`.
		scope: [ 'inserter' ], // Limit scope to inserter so it's not available in transformations.
		icon: interactiveVideoIcon,
		isDefault: true,
	},
	videoFileVariation,
	{
		name: 'videopress',
		title: __( 'Interactive VideoPress' ),
		attributes: { videoType: 'videopress' },
		innerBlocks: [
			[
				'core/embed',
				{
					providerNameSlug: 'videopress',
					responsive: true,
				},
			],
			timelineBlock,
		],
		isActive,
		scope,
		icon: embedVideoIcon,
	},
	{
		name: 'youtube',
		title: __( 'Interactive YouTube' ),
		attributes: { videoType: 'youtube' },
		innerBlocks: [
			[
				'core/embed',
				{
					providerNameSlug: 'youtube',
					responsive: true,
				},
			],
			timelineBlock,
		],
		isActive,
		scope,
		icon: embedYouTubeIcon,
	},
	{
		name: 'vimeo',
		title: __( 'Interactive Vimeo' ),
		attributes: { videoType: 'vimeo' },
		innerBlocks: [
			[
				'core/embed',
				{
					providerNameSlug: 'vimeo',
					responsive: true,
				},
			],
			timelineBlock,
		],
		isActive,
		scope,
		icon: embedVimeoIcon,
	},
];

export default variations;
