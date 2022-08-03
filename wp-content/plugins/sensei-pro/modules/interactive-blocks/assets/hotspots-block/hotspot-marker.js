/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Hotspot maker positioned on top of an image.
 *
 * @param {Object} props
 * @param {number} props.x         Left coordinate.
 * @param {number} props.y         Top coordinate.
 * @param {number} props.onClick   A method to passed from parent to select tooltip block when marker is clicked.
 * @param {string} props.className Class name for the component.
 */
export const HotspotMarker = ( { x, y, onClick, className, ...props } ) => {
	const style = {
		left: `${ x }%`,
		top: `${ y }%`,
	};

	const markerClicked = ( event ) => {
		event.stopPropagation();
		onClick();
	};
	const markerEntered = ( event ) => {
		if ( event.key === 'Enter' ) {
			markerClicked( event );
		}
	};
	className = classnames(
		'sensei-lms-image-hotspots__hotspot-marker',
		className
	);

	return (
		<button
			{ ...props }
			className={ className }
			style={ style }
			onClick={ markerClicked }
			onKeyPress={ markerEntered }
		>
			{ /* eslint-disable-next-line jsx-a11y/anchor-has-content,jsx-a11y/anchor-is-valid -- Interaction provided by button. */ }
			<a tabIndex="-1" />
		</button>
	);
};
