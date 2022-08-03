/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';

export const FILL_CONTENT_CLASS = 'sensei-interactive-block-fill-content';

/**
 * Deserialize the attributes string which was saved in the HTML.
 *
 * @param {string} serializedAttributes The attributes string.
 * @return {Object} The deserialized object.
 */
const deserializeAttributes = ( serializedAttributes ) =>
	JSON.parse( window.atob( serializedAttributes ) );

/**
 * Get information from element to render block in the frontend.
 *
 * @param {Object} element Element to extract attributes and inner blocks.
 *
 * @return {Object} Object containing attributes to be added to the frontend component and the
 *                  array of inner blocks to be restored.
 */
export const getBlockFrontendProps = ( element ) => {
	const attributes = deserializeAttributes( element.dataset.attributes );

	const fills = {};
	[ ...element.querySelectorAll( '.' + FILL_CONTENT_CLASS ) ].forEach(
		( e, index ) => {
			const blockAttributes = e.dataset.attributes
				? deserializeAttributes( e.dataset.attributes )
				: {};

			fills[ blockAttributes.slot || index ] = {
				reactElement: getInnerHtmlAsReact( e ),
				attributes: blockAttributes,
			};
		}
	);

	return {
		attributes,
		fills,
	};
};

/**
 * Get inner HTML from a DOM element and return as a React element.
 *
 * @param {Object} e DOM element.
 *
 * @return {Object} React element or `null` if content is empty.
 */
const getInnerHtmlAsReact = ( e ) =>
	e.innerHTML.trim() !== '' ? <RawHTML>{ e.innerHTML }</RawHTML> : null;
