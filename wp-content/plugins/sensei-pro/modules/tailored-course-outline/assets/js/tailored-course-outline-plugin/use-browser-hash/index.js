/**
 * WordPress dependencies
 */
import { useEffect, useState, useCallback } from '@wordpress/element';
const useBrowserHash = () => {
	const [ hash, setHash ] = useState( () => window.location.hash );

	useEffect( () => {
		const hashChangeHandler = () => {
			if ( hash !== window.location.hash ) {
				setHash( window.location.hash );
			}
		};

		window.addEventListener( 'hashchange', hashChangeHandler );

		return () => {
			window.removeEventListener( 'hashchange', hashChangeHandler );
		};
	}, [ hash ] );

	const updateHash = useCallback(
		( newHash ) => {
			if ( newHash !== hash ) {
				window.location.hash = newHash;
				setHash( newHash );
			}
		},
		[ hash ]
	);

	return [ hash, updateHash ];
};

export default useBrowserHash;
