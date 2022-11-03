/**
 * External dependencies
 */
/**
 * WordPress dependencies
 */
import { createElement, isValidElement, render } from '@wordpress/element';
import { isEmpty } from 'lodash';
import { Provider } from 'react-redux';

/**
 * Internal dependencies
 */
import { blocksStore } from './data';
import { useBlocksStore } from './data/use-blocks-store';

/**
 * @typedef {module:block-frontend/parse~FrontendBlock} FrontendBlock
 */

/**
 * Replace a block's DOM node with the rendered run function.
 *
 * @param {FrontendBlock} block
 */
export function runBlock( block ) {
	const { element } = block;
	render( renderBlock( block ), element );
}

/**
 * Render a runnable or static block.
 *
 * @param {FrontendBlock} block
 */
export function renderBlock( block ) {
	const { element, blockType, clientId, innerBlocks } = block;

	if ( ! blockType ) {
		if ( isValidElement( element ) ) {
			return element;
		}
		return (
			<RawElement
				key={ clientId }
				block={ block }
				html={ element.innerHTML }
			/>
		);
	}

	return (
		<Provider store={ blocksStore } key={ clientId }>
			<BlockComponent
				block={ block }
				children={ innerBlocks?.map( renderBlock ) ?? [] }
			/>
		</Provider>
	);
}

/**
 * Component to render a block instance manually.
 *
 * @param {FrontendBlock} block Block instance.
 */
export const BlockFrontend = ( { block } ) => {
	return renderBlock( block );
};

/**
 * Render the block's DOM element.
 *
 * @param {Object}        props
 * @param {FrontendBlock} props.block
 * @param {string}        props.html
 */
export function RawElement( { block, html } ) {
	const { element, blockProps } = block;

	return createElement( element.tagName.toLowerCase(), {
		...blockProps,
		dangerouslySetInnerHTML: { __html: html },
	} );
}

/**
 * Render the block's react component.
 *
 * @param {Object}        props
 * @param {FrontendBlock} props.block
 */
export function BlockComponent( { block, ...rest } ) {
	const { blockType, blockId } = block;
	const { run: Component } = blockType;

	const { attributes, setAttributes } = useBlocksStore( block );

	// Do not render the component if there are no attributes yet.
	if ( isEmpty( attributes ) ) {
		return null;
	}

	return (
		<Component
			{ ...block }
			{ ...rest }
			blockId={ blockId }
			attributes={ attributes }
			setAttributes={ setAttributes }
		/>
	);
}
