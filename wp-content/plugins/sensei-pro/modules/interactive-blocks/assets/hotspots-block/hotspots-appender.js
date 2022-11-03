/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ImageHotspots } from './elements';
import { HotspotBlock } from './hotspot-block';

/**
 * The max distance in pixedls that the hotspot can travel ouside the
 * hotspots image box.
 *
 * @member {number}
 */
const MAX_OUT_OF_BOX_THRESHOLD = 5;

/**
 * Get the position of a click relative to the image.
 *
 * @param {Event} event
 */
export const calculateRelativeClickPosition = ( event ) => {
	const { clientX, clientY, target } = event;
	const box = target.getBoundingClientRect();

	let x = clientX - box.left;
	let y = clientY - box.top;

	x = Math.min( x, box.width + MAX_OUT_OF_BOX_THRESHOLD );
	y = Math.min( y, box.height + MAX_OUT_OF_BOX_THRESHOLD );

	x = Math.max( x, -MAX_OUT_OF_BOX_THRESHOLD );
	y = Math.max( y, -MAX_OUT_OF_BOX_THRESHOLD );

	return {
		x: ( x / box.width ) * 100,
		y: ( y / box.height ) * 100,
	};
};

/**
 * Overlay to add and display markers.
 *
 * @param {Object}  props
 * @param {boolean} props.addingMarker Indicates whether adding marker mode is on.
 * @param {string}  props.clientId     The clientId of the containing block.
 */
export const HotspotsAppender = ( { addingMarker, clientId } ) => {
	const { insertBlock } = useDispatch( 'core/block-editor' );
	const innerBlocks = useSelect( ( select ) =>
		select( 'core/block-editor' ).getBlocks( clientId )
	);

	/**
	 * Add a new hotspot block.
	 *
	 * @param {Object} event Object representing the event.
	 */
	const addHotspotBlock = ( event ) =>
		addingMarker &&
		insertBlock(
			createBlock(
				HotspotBlock.name,
				calculateRelativeClickPosition( event )
			),
			innerBlocks.length,
			clientId,
			true
		);

	return (
		// eslint-disable-next-line jsx-a11y/click-events-have-key-events,jsx-a11y/no-static-element-interactions
		<ImageHotspots.Overlay
			onClick={ addHotspotBlock }
			className={ classnames( { 'adding-marker': addingMarker } ) }
		/>
	);
};
