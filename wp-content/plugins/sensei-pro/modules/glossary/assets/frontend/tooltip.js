/**
 * External dependencies
 */
import { debounce } from 'lodash';
import { computePosition, flip, shift } from '@floating-ui/dom';

/**
 * Create the tooltip container element.
 *
 * @return {HTMLElement} The tooltip HTML element.
 */
const createTooltipContainer = () => {
	const tooltip = document.createElement( 'div' );
	tooltip.setAttribute( 'class', 'sensei-glossary-tooltip' );
	document.body.appendChild( tooltip );

	// Keep showing the tooltip when hovered.
	tooltip.addEventListener( 'mouseenter', hideTooltip.cancel );
	tooltip.addEventListener( 'mouseleave', hideTooltip );

	return tooltip;
};

/**
 * Get the tooltip container element.
 *
 * @return {HTMLElement} The tooltip HTML element.
 */
const getTooltipContainer = () => {
	return (
		document.querySelector( '.sensei-glossary-tooltip' ) ||
		createTooltipContainer()
	);
};

/**
 * Event listener for hiding the tooltip when pressing ESC.
 *
 * @param {KeyboardEvent} event
 */
const hideOnEscListener = ( event ) => {
	if ( event.key === 'Escape' ) {
		hideTooltip();
	}
};

/**
 * Hide the tooltip after a delay.
 */
export const hideTooltip = debounce( () => {
	const tooltip = getTooltipContainer();

	tooltip.innerHTML = '';
	tooltip.style.display = 'none';
}, 200 );

/**
 * Show the tooltip.
 *
 * @param {HTMLElement} target
 * @param {Node}        content
 */
export const showTooltip = ( target, content ) => {
	const tooltip = getTooltipContainer();

	// Don't hide the tooltip when quickly switching targets.
	hideTooltip.cancel();

	// Calculate the tooltip position relative to the target element.
	computePosition( target, tooltip, {
		middleware: [
			flip(), // Changes the placement of the tooltip to the top in order to keep it in view.
			shift(), // Moves the tooltip away from the edge of the screen.
		],
	} ).then( ( { x, y } ) => {
		Object.assign( tooltip.style, {
			left: `${ x }px`,
			top: `${ y }px`,
		} );
	} );

	// Hide the tooltip on ESC.
	document.addEventListener( 'keydown', hideOnEscListener, { once: true } );

	tooltip.replaceChildren( content );
	tooltip.style.display = 'block';
};
