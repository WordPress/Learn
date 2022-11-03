/**
 * WordPress dependencies
 */
import { useCallback, useState, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import useDragging from '../use-dragging';
import { calculateRelativeClickPosition } from './hotspots-appender';
import { IMAGE_HOTSPOST_CLASS_NAME } from './elements';

/**
 * The distance in pixels that the hotspot must travel in order to
 * update the position.
 *
 * @member {number}
 */
const POSITION_CHANGE_THRESHOLD = 3;

export const useHotspotDragging = ( {
	clientId,
	hotspotRef,
	onPositionChange,
} ) => {
	const [ draggedDiff, setDraggedDiff ] = useState( {} );
	const [ dragCoords, setDragCoords ] = useState( {} );
	const imageHotspotsRef = useRef( null );
	const [ imageHotspotsBlockId ] = useSelect( ( select ) => {
		return select( 'core/block-editor' ).getBlockParentsByBlockName(
			clientId,
			'sensei-pro/image-hotspots'
		);
	} );

	const calculateCoords = useCallback(
		( ev ) => {
			if ( imageHotspotsRef.current ) {
				return calculateRelativeClickPosition( {
					target: imageHotspotsRef.current,
					clientX: ev.clientX,
					clientY: ev.clientY,
				} );
			}
		},
		[ imageHotspotsBlockId ]
	);

	const onDragStart = useCallback( () => {
		imageHotspotsRef.current = hotspotRef.current.closest(
			`.${ IMAGE_HOTSPOST_CLASS_NAME }`
		);
	}, [ hotspotRef ] );

	const onDragEnd = useCallback( () => {
		const changedPosition =
			Math.abs( draggedDiff.x ) > POSITION_CHANGE_THRESHOLD ||
			Math.abs( draggedDiff.y ) > POSITION_CHANGE_THRESHOLD;

		if ( changedPosition ) {
			onPositionChange( dragCoords );
		} else {
			setDragCoords( {} );
		}
	}, [ onPositionChange, draggedDiff, dragCoords ] );

	const onDrag = useCallback(
		( position ) => {
			setDraggedDiff( { x: position.diffX, y: position.diffY } );
			setDragCoords(
				calculateCoords( {
					clientX: position.clientX,
					clientY: position.clientY,
				} )
			);
		},
		[ calculateCoords ]
	);

	const { draggableProps } = useDragging( {
		onDrag,
		onDragStart,
		onDragEnd,
	} );

	return {
		draggableProps,
		dragCoords,
	};
};
