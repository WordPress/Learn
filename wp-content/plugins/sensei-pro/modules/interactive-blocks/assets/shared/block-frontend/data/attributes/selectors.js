/**
 * Retrieves the attributes of a block.
 *
 * @param {Object} state   The blocks state.
 * @param {string} blockId The id of a block.
 * @return {Object} The attributes of a block.
 */
export const getBlockAttributes = ( state, blockId = '' ) =>
	state.attributes?.[ blockId ] || {};

/**
 * Returns the ids of the blocks that are required for lesson to be complete.
 *
 * @param {Object} state The blocks state.
 * @return {string[]} The ids of the blocks that are required.
 */
export const getRequiredBlockIds = ( state ) =>
	filterRequiredBlockIds( state, Object.keys( state.attributes ) );

/**
 * Returns the ids of the blocks that are required for lesson to be complete,
 * based on the blockIds array.
 *
 * @param {Object}   state    The blocks state.
 * @param {string[]} blockIds The list of block IDs to filter on.
 * @return {string[]} The ids of the blocks from blockIds that are required.
 */
export const filterRequiredBlockIds = ( state, blockIds ) =>
	blockIds.filter( ( blockId ) => state.attributes?.[ blockId ].required );

/**
 * Tells if the block is in "completed" state or not.
 *
 * @param {Object} state   The blocks state.
 * @param {string} blockId The id of a block.
 * @return {boolean} Returns true if the block is in completed state.
 *                   False otherwise.
 */
export const isBlockCompleted = ( state, blockId = '' ) =>
	!! state.attributes?.[ blockId ]?.completed;

/**
 * Checks a list of blocks if they are completed or not.
 *
 * @param {Object}   state    The blocks state.
 * @param {string[]} blockIds The ids of the blocks to check against.
 * @return {boolean[]} The list of booleans indicating if the corresponding block is completed or not.
 */
export const areBlocksCompleted = ( state, blockIds = [] ) =>
	blockIds.map( ( blockId ) => isBlockCompleted( state, blockId ) );

/**
 * Returns true if all the passed requiredBlocksIds were completed, or false
 * otherwise.
 *
 * @param {Object}   state             The blocks state.
 * @param {string[]} requiredBlocksIds The list of required blocks IDs.
 * @return {boolean} true if all the required blocks ids are completed, false otherwise.
 */
export const areRequiredBlocksCompleted = ( state, requiredBlocksIds ) => {
	// If there are no required blocks then we're good.
	if ( ! requiredBlocksIds.length ) {
		return true;
	}

	const completedBlocksCount = areBlocksCompleted(
		state,
		requiredBlocksIds
	).filter( ( completed ) => completed === true ).length;

	return requiredBlocksIds.length <= completedBlocksCount;
};

/**
 * Block attributes selectors
 */
export const selectors = {
	getBlockAttributes,
	getRequiredBlockIds,
	filterRequiredBlockIds,
	isBlockCompleted,
	areBlocksCompleted,
	areRequiredBlocksCompleted,
};
