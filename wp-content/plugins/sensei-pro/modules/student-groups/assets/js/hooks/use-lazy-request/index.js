/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

const useLazyRequest = ( request, dependencies = [] ) => {
	const [ requestState, setRequestState ] = useState( {
		status: 'IDLE',
		response: null,
		error: null,
	} );

	const run = useCallback(
		async ( ...params ) => {
			setRequestState( {
				status: 'LOADING',
				response: null,
			} );

			try {
				const response = await request( ...params );
				setRequestState( {
					status: 'IDLE',
					response,
					error: null,
				} );
				return response;
			} catch ( error ) {
				setRequestState( {
					status: 'IDLE',
					response: null,
					error,
				} );
			}
		},
		[ request, ...dependencies ]
	);

	return {
		isLoading: requestState.status === 'LOADING',
		run,
		response: requestState.response,
		hasError: requestState.error !== null,
		error: requestState.error,
	};
};

export default useLazyRequest;
