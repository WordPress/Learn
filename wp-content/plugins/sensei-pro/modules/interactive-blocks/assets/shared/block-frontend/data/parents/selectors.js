/**
 * Retrieves the direct parent of the block
 *
 * @param {Object} state   The blocks state.
 * @param {string} blockId The id of a block.
 * @return {string|null} The parent block's blockId.
 */
export const getParentBlock = ( state, blockId = '' ) =>
	state.parents?.[ blockId ];

/**
 * Retrieves all the ancestors of the block.
 *
 * @param {Object} state   The blocks state.
 * @param {string} blockId The id of a block.
 * @return {string[]|null} BlockIds of the block's ancestors.
 */
export const getAncestorBlocks = ( state, blockId = '' ) => {
	const parent = state.parents?.[ blockId ];

	return parent
		? [ parent, ...( getAncestorBlocks( state, parent ) ?? [] ) ]
		: null;
};

/**
 * Get direct descendants of the given block.
 *
 * @param {Object} state
 * @param {string} blockId
 * @return {string[]} BlockIds of direct descendants
 */
export const getDirectDescendantBlocks = ( state, blockId = '' ) => {
	return Object.entries( state.parents ?? {} ).reduce(
		( m, [ child, parent ] ) =>
			blockId === parent ? [ ...m, child ] : m,
		[]
	);
};

/**
 * Get a list of all descendants of the given block.
 *
 * @param {Object} state
 * @param {string} blockId
 * @return {string[]} Descendant blockIds
 */
export const getDescendantBlockList = ( state, blockId = '' ) => {
	const result = [];
	for ( const id of getDirectDescendantBlocks( state, blockId ) ) {
		result.push( id, ...getDescendantBlockList( state, id ) );
	}
	return result;
};

/**
 * Get a hierarchy tree of all descendants of the given block.
 *
 * @param {Object} state
 * @param {string} blockId
 * @return {Object} Descendant blockIds
 */
export const getDescendantBlockHierarchy = ( state, blockId = '' ) => {
	const result = {};
	for ( const id of getDirectDescendantBlocks( state, blockId ) ) {
		result[ id ] = getDescendantBlockHierarchy( state, id );
	}
	return result;
};

/**
 * Block parent selectors
 */
export const selectors = {
	getParentBlock,
	getAncestorBlocks,
	getDirectDescendantBlocks,
	getDescendantBlockList,
	getDescendantBlockHierarchy,
};
