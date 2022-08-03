/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { calculateRelativeClickPosition } from './hotspots-appender';
import { IMAGE_HOTSPOST_CLASS_NAME } from './elements';

/**
 * The distance in pixels that the hotspot must travel in order to
 * update the position.
 *
 * @member {number}
 */
const POSITION_CHANGE_THRESHOLD = 3;

export const useHotspotDragging = ( { clientId, onPositionChange } ) => {
	const [ dragging, setDragging ] = useState( false );
	const [ initialPosition, setInitialPosition ] = useState( {} );
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

	const onMouseDown = useCallback( ( ev ) => {
		ev.preventDefault();
		imageHotspotsRef.current = ev.target.closest(
			`.${ IMAGE_HOTSPOST_CLASS_NAME }`
		);
		setDragging( true );
		setInitialPosition( {
			x: ev.clientX,
			y: ev.clientY,
		} );
	}, [] );

	const onMouseUp = useCallback(
		( ev ) => {
			if ( ! dragging ) {
				return;
			}

			ev.preventDefault();
			setDragging( false );
			const changedPosition =
				Math.abs( initialPosition.x - ev.clientX ) >
					POSITION_CHANGE_THRESHOLD ||
				Math.abs( initialPosition.y - ev.clientY ) >
					POSITION_CHANGE_THRESHOLD;
			if ( changedPosition ) {
				onPositionChange( calculateCoords( ev ) );
			} else {
				setDragCoords( {} );
			}
		},
		[ dragging, calculateCoords, initialPosition ]
	);

	const onMouseMove = useCallback(
		( ev ) => {
			if ( dragging ) {
				setDragCoords( calculateCoords( ev ) );
			}
		},
		[ dragging, calculateCoords ]
	);

	useEffect( () => {
		document.addEventListener( 'mouseup', onMouseUp );
		document.addEventListener( 'mousemove', onMouseMove );
		return () => {
			document.removeEventListener( 'mouseup', onMouseUp );
			document.removeEventListener( 'mousemove', onMouseMove );
		};
	}, [ onMouseUp, onMouseMove ] );

	return {
		onMouseDown,
		dragCoords,
		dragging,
	};
};
