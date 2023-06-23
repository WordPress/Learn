/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { showTooltip, hideTooltip } from './tooltip';

/**
 * Initialize the tooltip for a glossary phrase element.
 *
 * @param {HTMLElement} element
 */
const initTooltip = ( element ) => {
	const template = document.getElementById(
		element.dataset.senseiGlossaryPhraseId
	);

	const showTooltipListener = ( event ) => {
		showTooltip( event.target, template.content.cloneNode( true ) );
	};

	element.addEventListener( 'click', showTooltipListener );
	element.addEventListener( 'mouseenter', showTooltipListener );
	element.addEventListener( 'mouseleave', hideTooltip );
	element.addEventListener( 'focus', showTooltipListener );
	element.addEventListener( 'blur', hideTooltip );
};

/**
 * Initialize the glossary.
 */
export const initGlossary = () => {
	document
		.querySelectorAll( '.sensei-glossary-phrase' )
		.forEach( initTooltip );
};

domReady( initGlossary );
