/**
 * WordPress dependencies
 */
import { renderToString } from '@wordpress/element';

export const renderToDOM = ( html ) => {
	const rootNode = document.createElement( 'div' );
	rootNode.innerHTML = renderToString( html );
	return rootNode;
};
