/**
 * Returns if a block is valid or not for the interactive video block.
 *
 * @param {Object} block The block to verify.
 * @return {boolean} true if the block is valid, false otherwise.
 */
const isValidVideoBlock = ( block ) =>
	( block?.name === 'core/embed' &&
		[ 'videopress', 'youtube', 'vimeo' ].includes(
			block?.attributes?.providerNameSlug
		) ) ||
	block?.name === 'core/video';

export default isValidVideoBlock;
