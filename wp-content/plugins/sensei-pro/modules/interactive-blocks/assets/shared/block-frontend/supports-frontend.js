/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { hasBlockSupport } from '@wordpress/blocks';
import { RawHTML } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import {
	BLOCK_DATA_ATTRIBUTE,
	INNER_BLOCKS_DELIMITER,
	serializeAttributes,
} from './parse';

/**
 * Serialize block attributes and add as blockProp.
 *
 * @param {Object} props      Extra block props.
 * @param {Object} blockType  Block defintion.
 * @param {Object} attributes Block attributes.
 */
const saveBlockAttributes = ( props, blockType, attributes ) => {
	if ( ! hasBlockSupport( blockType, 'sensei.frontend' ) ) {
		return props;
	}

	return {
		...props,
		[ BLOCK_DATA_ATTRIBUTE ]: serializeAttributes( attributes ),
	};
};

/**
 * Add a children prop to the block's save function that contains the rendered and wrapped inner blocks.
 *
 * @param {Object} blockType Block definition.
 */
const addSaveWrapper = ( blockType ) => {
	if ( ! hasBlockSupport( blockType, 'sensei.frontend' ) ) {
		return blockType;
	}

	return {
		...blockType,
		save: ( props ) => {
			const children = (
				<InnerBlocksWrapper>
					<InnerBlocks.Content />
				</InnerBlocksWrapper>
			);
			const blockProps = useBlockProps.save();
			return blockType.save( { ...props, children, blockProps } );
		},
	};
};

addFilter(
	'blocks.getSaveContent.extraProps',
	'sensei-pro/run-block-support/serialize-attributes',
	saveBlockAttributes
);

addFilter(
	'blocks.registerBlockType',
	'sensei-pro/run-block-support/wrap-inner-blocks',
	addSaveWrapper
);

/**
 * Inner blocks wrapper to mark inner blocks manually.
 *
 * @param {Object} props
 * @param {Array}  props.children
 */
export const InnerBlocksWrapper = ( { children } ) => {
	return (
		<>
			<RawHTML>{ `<!--${ INNER_BLOCKS_DELIMITER }-->\n` }</RawHTML>
			{ children }
			<RawHTML>{ `\n<!--/${ INNER_BLOCKS_DELIMITER }-->` }</RawHTML>
		</>
	);
};
