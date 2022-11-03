/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Returns a callback that always returns an empty object for the given blockName.
 *
 * @param {string} blockName The block name to filter on.
 * @return {Function} Return the callback that ignores persisted data for the given blockName.
 */
const ignorePersistedAttributesCallback = ( blockName ) => (
	persistedAttributes,
	blockId,
	block
) => {
	return block.blockType.name === blockName ? {} : persistedAttributes;
};

/**
 * Ignore the persisted attributes for the block specified by blockName.
 *
 * @param {string} blockName The block name to ignore.
 */
const ignorePersistedAttributes = ( blockName ) => {
	addFilter(
		'sensei.blockFrontend.persistedAttributes',
		'sensei-pro/ignorePersist/' + blockName,
		ignorePersistedAttributesCallback( blockName )
	);
};

export default ignorePersistedAttributes;
