/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
/**
 * External dependencies
 */
import { useDispatch, useSelector } from 'react-redux';
/**
 * Internal dependencies
 */
import * as parentsStore from './parents';
import * as attributesStore from './attributes';
import { getPersistedState } from './persistState';

const {
	selectors: { getBlockAttributes },
	actions: { setAttributes: setBlockAttributes },
} = attributesStore;
const {
	actions: { setParent },
} = parentsStore;

/**
 * @typedef {module:block-frontend/parse~FrontendBlock} FrontendBlock
 */

/**
 * The last saved state in local storage.
 */
const persistedState = getPersistedState();

/**
 * Register the block in the blocks store. Merge persisted and parsed attributes.
 *
 * @param {FrontendBlock} block
 */
export function useBlocksStore( block ) {
	const { blockType, blockId, attributes: defaultAttributes, parent } = block;

	const persistedAttributes = useRef(
		getBlockAttributes( persistedState, blockId )
	);

	const dispatch = useDispatch();

	// Set the attributes for the block in initial mount.
	useEffect( () => {
		/**
		 * Allows to modify blocks' persistedAttributes before they are
		 * added to the store as initial state for blocks.
		 *
		 * @since 1.2.0
		 *
		 * @name sensei.blockFrontend.persistedAttributes Filter what attributes are persisted.
		 *
		 * @param {Object}        persistedAttributes The attribures that were stored in local storage
		 *                                            that are going to be used as initial state for the block.
		 * @param {string}        blockId             The block id.
		 * @param {FrontendBlock} block               The frontend block object.
		 */
		const finalPersistedAttributes = applyFilters(
			'sensei.blockFrontend.persistedAttributes',
			persistedAttributes.current,
			blockId,
			block
		);
		const attributes = {
			blockId,
			blockType,
			...defaultAttributes,
			...finalPersistedAttributes,
		};

		dispatch( setBlockAttributes( blockId, attributes ) );
		dispatch( setParent( blockId, parent ) );
	}, [] );

	const attributes = useSelector( ( state ) =>
		getBlockAttributes( state, blockId )
	);

	const setAttributes = useCallback(
		( attr ) => {
			dispatch( setBlockAttributes( blockId, attr ) );
		},
		[ blockId ]
	);

	return { attributes, setAttributes };
}
