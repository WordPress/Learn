/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { parseBlocks } from './parse';
import { runBlock } from './render';

export { BlockFrontend, RawElement } from './render';

/**
 * Find and mount all runnable blocks on the page.
 */
export function runBlocks() {
	domReady( () => {
		parseBlocks().forEach( runBlock );
	} );
}

export { registerBlockFrontend } from './registry';
