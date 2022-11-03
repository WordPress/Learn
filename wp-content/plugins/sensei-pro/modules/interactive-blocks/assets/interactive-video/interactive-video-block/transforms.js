/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';

/**
 * Migrate attributes from video block to Interactive Video block.
 *
 * @param {Object} attributes       Video/Embed block attributes.
 * @param {string} attributes.align Video/Embed block align attribute.
 *
 * @return {Object} Object with the migrated attributes.
 */
export const migrateAttributes = ( { align } ) => {
	if ( [ 'wide', 'full' ].includes( align ) ) {
		return { align };
	}

	return {};
};

const transforms = {
	from: [
		{
			type: 'block',
			blocks: [ 'core/video' ],
			transform( attributes ) {
				return createBlock(
					'sensei-pro/interactive-video',
					migrateAttributes( attributes ),
					[
						createBlock( 'core/video', attributes ),
						createBlock( 'sensei-pro/timeline' ),
					]
				);
			},
		},
		{
			type: 'block',
			blocks: [ 'core/embed' ],
			transform( attributes ) {
				return createBlock(
					'sensei-pro/interactive-video',
					migrateAttributes( attributes ),
					[
						createBlock( 'core/embed', attributes ),
						createBlock( 'sensei-pro/timeline' ),
					]
				);
			},
			isMatch: ( { providerNameSlug } ) =>
				providerNameSlug &&
				[ 'videopress', 'youtube', 'vimeo' ].includes(
					providerNameSlug
				),
		},
	],
};

export default transforms;
