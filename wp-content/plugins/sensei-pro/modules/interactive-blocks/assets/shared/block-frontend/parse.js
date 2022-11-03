/**
 * External dependencies
 */
import { v4 as uuid } from 'uuid';
// eslint-disable-next-line import/no-unresolved -- Issue with eslint-import-resolver-webpack: https://github.com/import-js/eslint-plugin-import/issues/2293
import { attributesToProps } from 'html-react-parser';

/**
 * Internal dependencies
 */
import {
	getBlockTypeSelector,
	getFrontendBlockType,
	getFrontendBlockTypes,
} from './registry';
import { BLOCK_ID_ATTRIBUTE } from '../supports-block-id';

export const INNER_BLOCKS_DELIMITER = 'sensei:inner-blocks';

export const BLOCK_DATA_ATTRIBUTE = 'data-sensei-wp-block';
export const BLOCK_NAME_ATTRIBUTE = 'data-block-name';

/**
 * @module block-frontend/parse
 * @typedef {module:block-frontend/registry~FrontendBlockType} FrontendBlockType
 *
 * @typedef FrontendBlock
 * Block instance.
 *
 * @property {FrontendBlockType} blockType   Block type configuration
 * @property {Element}           element     DOM Element
 * @property {string}            clientId    Block ID.
 * @property {string}            blockId     Persisted block ID.
 * @property {string}            parent      Parent block ID.
 * @property {Object}            attributes  Block attributes.
 * @property {Object}            blockProps  Block wrapper props.
 * @property {FrontendBlock[]}   innerBlocks Inner block elements.
 */

/**
 * Find and parse all blocks on the page.
 *
 * @param {Element|Document} [root] Root DOM element. Defaults to the whole document.
 * @return {FrontendBlock[]} Top-level blocks.
 */
export function parseBlocks( root = document ) {
	const findAndMarkBlockElements = ( blockType ) => {
		const elements = root.querySelectorAll(
			getBlockTypeSelector( blockType )
		);
		return Array.from( elements ).map( ( element ) => {
			element.setAttribute( BLOCK_NAME_ATTRIBUTE, blockType.name );
			return {
				element,
				blockType,
			};
		} );
	};

	const isRootBlock = ( { element } ) =>
		! element.parentElement?.closest( `[${ BLOCK_NAME_ATTRIBUTE }]` );

	const blockTypes = getFrontendBlockTypes();

	const blockElements = blockTypes.map( findAndMarkBlockElements ).flat();
	const rootBlocks = blockElements.filter( isRootBlock ).map( parseBlock );

	return rootBlocks;
}

/**
 * Parse a block from a DOM element, including its inner blocks.
 *
 * @param {Object}            settings             Settings of the block being processed.
 * @param {Element}           settings.element     The HTML element being processed.
 * @param {FrontendBlockType} [settings.blockType] The block type registered using registerBlockFrontend.
 * @param {string}            settings.parent      The Block ID of the Parent Block.
 *
 * @return {FrontendBlock} Block data.
 */
const parseBlock = ( { element, blockType, parent = null } ) => {
	const clientId = uuid();
	if ( ! blockType ) {
		blockType = getFrontendBlockType(
			element.getAttribute( BLOCK_NAME_ATTRIBUTE )
		);
	}
	try {
		const {
			[ BLOCK_DATA_ATTRIBUTE ]: serializedBlockAttributes,
			...blockProps
		} = getElementAttributesAsProps( element );
		const attributes = serializedBlockAttributes
			? deserializeAttributes( serializedBlockAttributes )
			: {};

		let innerBlocks = [];

		const blockId =
			attributes.blockId || blockProps[ BLOCK_ID_ATTRIBUTE ] || clientId;

		if ( blockType ) {
			innerBlocks = getInnerBlockElements( element ).map(
				( innerBlockElement ) =>
					parseBlock( {
						element: innerBlockElement,
						parent: blockId,
					} )
			);
		}

		return {
			type: blockType ? 'runnable' : 'static',
			blockId,
			clientId,
			blockType,
			parent,
			attributes,
			element,
			blockProps,
			innerBlocks,
		};
	} catch ( err ) {
		// eslint-disable-next-line no-console
		console.error( 'Invalid Block', err );
		return null;
	}
};

/**
 * Find elements that are inner blocks of a block.
 *
 * @param {Element} element DOM node
 */
function getInnerBlockElements( element ) {
	const delimetersXpathResult = element.ownerDocument.evaluate(
		'.//comment()',
		element,
		null,
		XPathResult.ANY_TYPE
	);
	let current = null;
	const delimeterTags = [];
	while ( ( current = delimetersXpathResult.iterateNext() ) ) {
		delimeterTags.push( current );
	}

	const startTag = delimeterTags.find(
		( tag ) => INNER_BLOCKS_DELIMITER === tag.textContent
	);
	const endTag = [ ...delimeterTags ]
		.reverse()
		.find( ( tag ) => `/${ INNER_BLOCKS_DELIMITER }` === tag.textContent );

	if ( ! startTag || ! endTag ) return [];

	const innerBlockElements = [];
	let node = startTag.nextSibling;
	while ( node && node !== endTag ) {
		if ( Node.ELEMENT_NODE === node.nodeType ) {
			innerBlockElements.push( node );
		}
		node = node.nextSibling;
	}

	return innerBlockElements;
}

/**
 * Convert element attributes to a prop object.
 *
 * @param {Element} element DOM node
 */
export function getElementAttributesAsProps( element ) {
	const attributes = Object.assign(
		{},
		...Array.from( element.attributes, ( { name, value } ) => ( {
			[ name ]: value,
		} ) )
	);
	return attributesToProps( attributes );
}

/**
 * Serialize the attributes object to be saved into the HTML.
 *
 * @param {Object} attributes The attributes object.
 * @return {string} The serialized string.
 */
export const serializeAttributes = ( attributes ) =>
	JSON.stringify( sortKeys( attributes ) );

/**
 * Deserialize the attributes string which was saved in the HTML.
 *
 * @param {string} serializedAttributes The attributes string.
 * @return {Object} The deserialized object.
 */
export const deserializeAttributes = ( serializedAttributes ) =>
	JSON.parse( serializedAttributes );

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
