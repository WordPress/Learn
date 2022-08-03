/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { FILL_CONTENT_CLASS } from './interactive-blocks-helper';

/**
 * Serialize the attributes object to be saved into the HTML.
 *
 * @param {Object} attributes The attributes object.
 * @return {string} The serialized string.
 */
const serializeAttributes = ( attributes ) =>
	window.btoa( JSON.stringify( sortKeys( attributes ) ) );

/**
 * Sort the given object by keys. This only goes one level deep.
 *
 * @param {Object} object The object to sort.
 * @return {Object} The sorted object.
 */
const sortKeys = ( object ) => {
	const sorted = {};
	const keys = Object.keys( object ).sort();
	keys.forEach( ( key ) => {
		sorted[ key ] = object[ key ];
	} );

	return sorted;
};

/**
 * Save for blocks with inner blocks.
 * The inner content will be hidden to be restored in the frontend render.
 *
 * @param {Object} props            Component props.
 * @param {Object} props.attributes Block attributes.
 */
export const BlockWithInnerBlocksSave = ( { attributes } ) => {
	const blockProps = useBlockProps.save();

	return (
		<>
			<div
				{ ...blockProps }
				data-attributes={ serializeAttributes( attributes ) }
			>
				<div className="screen-reader-text">
					<InnerBlocks.Content />
				</div>
			</div>
		</>
	);
};

/**
 * Save for an inner block that needs to be restored later.
 *
 * @param {Object} props            Component props.
 * @param {Object} props.attributes Block attributes.
 */
export const InnerBlockSave = ( { attributes } ) => {
	const blockProps = useBlockProps.save();
	return (
		<div
			{ ...blockProps }
			className={ classnames( blockProps.className, FILL_CONTENT_CLASS ) }
			data-attributes={ serializeAttributes( attributes ) }
		>
			<InnerBlocks.Content />
		</div>
	);
};
