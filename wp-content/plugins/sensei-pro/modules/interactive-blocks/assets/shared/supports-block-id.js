/**
 * External dependencies
 */
import { isObject, merge, noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { useEffect, useMemo } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { getBlockSupport } from '@wordpress/blocks';

/**
 * The default name of the html attribute that is used to store
 * block id in the block html content.
 *
 * @member {string}
 */
export const BLOCK_ID_ATTRIBUTE = 'data-sensei-block-id';

/**
 * Filters registered block settings, extending attributes with anchor using ID
 * of the first node.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
export function addBlockIdSupport( settings ) {
	const supports = getBlockSupport( settings, 'sensei', {} );
	if ( ! supports.blockId ) {
		return settings;
	}

	const blockId = merge(
		{
			type: 'string',
			source: 'attribute',
			attribute: BLOCK_ID_ATTRIBUTE,
			selector: `[${ BLOCK_ID_ATTRIBUTE }]`,
		},
		isObject( supports.blockId ) ? supports.blockId : {}
	);

	// Set blockId attribute
	settings.attributes = {
		...settings.attributes,
		blockId,
	};

	return settings;
}

/**
 * Track what block is using a blockId.
 */
const blockIdRegistry = {};

/**
 * Check if a blockId is already being used by another block.
 *
 * @param {Object} props                    Block props.
 * @param {Object} props.attributes         Block attributes.
 * @param {string} props.attributes.blockId BlockId attribute.
 * @param {string} props.clientId           Block clientId.
 */
function useIsDuplicate( { attributes: { blockId }, clientId } ) {
	return useMemo( () => {
		if ( ! blockId ) {
			return false;
		}
		if ( ! blockIdRegistry[ blockId ] ) {
			blockIdRegistry[ blockId ] = clientId;
		} else if ( clientId !== blockIdRegistry[ blockId ] ) {
			return true;
		}
		return false;
	}, [ blockId, clientId ] );
}

/**
 * Override the default edit UI to set the blockId attribute
 * assigning the custom class name, if block supports custom class name.
 *
 * @param {WPComponent} BlockEdit Original component.
 *
 * @return {WPComponent} Wrapped component.
 */
export const withBlockIdSupport = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const support = getBlockSupport( props.name, 'sensei', {} );

		const { attributes, setAttributes, clientId } = props;
		const {
			__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = noop,
		} = useDispatch( 'core/block-editor' );

		const isDuplicate = useIsDuplicate( props );

		useEffect( () => {
			if ( ! support.blockId || 'function' !== typeof setAttributes ) {
				return;
			}

			if ( ! attributes.blockId || isDuplicate ) {
				markNextChangeAsNotPersistent();
				setAttributes( { blockId: clientId } );
			}
		}, [
			support.blockId,
			clientId,
			attributes.blockId,
			markNextChangeAsNotPersistent,
			isDuplicate,
		] );

		return <BlockEdit { ...props } />;
	};
}, 'withBlockIdSupport' );

/**
 * Updates the block save props to include the blockId attribute.
 *
 * @param {Object} extraProps Additional props applied to save element.
 * @param {Object} blockType  Block type.
 * @param {Object} attributes Current block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
export function saveBlockId( extraProps, blockType, attributes ) {
	const support = getBlockSupport( blockType, 'sensei', {} );

	if ( ! support.blockId ) {
		return extraProps;
	}
	const blockId = blockType.attributes.blockId || {};
	return {
		...extraProps,
		[ blockId.attribute ]: attributes.blockId,
	};
}

addFilter(
	'blocks.registerBlockType',
	'sensei/extend-supports/blockId/addBlockIdSupport',
	addBlockIdSupport
);

addFilter(
	'editor.BlockEdit',
	'sensei/extend-supports/blockId/withBlockIdSupport',
	withBlockIdSupport
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'sensei/extend-supports/saveBlockId',
	saveBlockId
);
