/**
 * External dependencies
 */
import { useVideoDuration } from 'sensei/assets/shared/helpers/player';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Get the point position style.
 *
 * @param {number} time   The time of the point in seconds.
 * @param {Object} player Player instance.
 *
 * @return {Object} The style object.
 */
const useBreakPointPositionStyle = ( time, player ) => {
	const [ position, setPosition ] = useState();
	const duration = useVideoDuration( player );

	useEffect( () => {
		if ( duration ) {
			setPosition( ( time / duration ) * 100 );
		}
	}, [ time, duration ] );

	if ( undefined !== position ) {
		return {
			left: position + '%',
		};
	}

	return {
		display: 'none',
	};
};

export default useBreakPointPositionStyle;
