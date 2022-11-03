/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';

/**
 * Hook to get a previous value.
 *
 * @param {*} value Current value.
 *
 * @return {*} Previous value.
 */
const usePrevious = ( value ) => {
	const ref = useRef();

	useEffect( () => {
		ref.current = value;
	}, [ value ] );

	return ref.current;
};

export default usePrevious;
