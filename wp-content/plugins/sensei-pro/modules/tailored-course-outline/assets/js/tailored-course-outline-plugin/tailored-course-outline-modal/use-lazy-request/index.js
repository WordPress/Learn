/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

const useLazyRequest = ( request, dependencies = [] ) => {
	const [ requestState, setRequestState ] = useState( {
		status: 'IDLE',
		response: null,
		error: null,
		completed: false,
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
					completed: true,
					response,
					error: null,
				} );
				return response;
			} catch ( error ) {
				setRequestState( {
					status: 'IDLE',
					response: null,
					error,
					completed: false,
				} );
			}
		},
		[ request, ...dependencies ]
	);

	return {
		isLoading: requestState.status === 'LOADING',
		run,
		response: requestState.response,
		hasError: Boolean( requestState.error ),
		error: requestState.error,
		hasCompleted: requestState.completed,
	};
};

export default useLazyRequest;
