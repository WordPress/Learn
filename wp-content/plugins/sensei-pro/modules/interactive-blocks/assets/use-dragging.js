/**
 * WordPress dependencies
 */
import { useEffect, useState, useCallback } from '@wordpress/element';

/**
 * A dragging hook. Notice that it doesn't use the `draggable` native feature because it's
 * disabled in the editor by Gutenberg.
 *
 * @param {Object}   options             Hook options.
 * @param {Function} options.onDrag      Drag callback.
 * @param {Function} options.onDragStart Drag start callback.
 * @param {Function} options.onDragEnd   Drag end callback.
 *
 * @return {Object} Object containing draggable props, and isDragging.
 */
export const useDragging = ( {
	onDrag = () => {},
	onDragStart = () => {},
	onDragEnd = () => {},
} ) => {
	const [ isDragging, setIsDragging ] = useState( false );
	const [ initialPosition, setInitialPosition ] = useState( {} );

	/**
	 * Mouse down event - Start dragging.
	 */
	const onMouseDown = useCallback(
		( e ) => {
			e.preventDefault();
			onDragStart();
			setIsDragging( true );
			setInitialPosition( {
				x: e.clientX,
				y: e.clientY,
			} );
		},
		[ onDragStart ]
	);

	/**
	 * Mouse up event - end dragging.
	 */
	const onMouseUp = useCallback(
		( e ) => {
			e.preventDefault();
			if ( isDragging ) {
				setIsDragging( false );
				onDragEnd();
			}
		},
		[ isDragging, onDragEnd ]
	);

	/**
	 * Mouse move event - dragging.
	 */
	const onMouseMove = useCallback(
		( e ) => {
			if ( isDragging ) {
				onDrag( {
					diffX: e.clientX - initialPosition.x,
					diffY: e.clientY - initialPosition.y,
					clientX: e.clientX,
					clientY: e.clientY,
				} );
			}
		},
		[ isDragging, initialPosition, onDrag ]
	);

	useEffect( () => {
		const editorCanvasIframe = document.querySelector(
			'iframe[name="editor-canvas"]'
		);
		const doc = editorCanvasIframe?.contentDocument || document;

		doc.addEventListener( 'mouseup', onMouseUp );
		doc.addEventListener( 'mousemove', onMouseMove );

		return () => {
			doc.removeEventListener( 'mouseup', onMouseUp );
			doc.removeEventListener( 'mousemove', onMouseMove );
		};
	}, [ onMouseUp, onMouseMove ] );

	return {
		draggableProps: { onMouseDown },
		isDragging,
	};
};

export default useDragging;
