/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';

const searchResource = async (
	resource,
	term = '',
	exclude = [],
	fields = []
) => {
	const params = new URLSearchParams( {
		per_page: 100,
		search: term,
		_fields: fields.join( ',' ),
		exclude: exclude.join( ',' ),
	} );

	return apiFetch( {
		path: `/wp/v2/${ resource }?` + params.toString(),
	} );
};

const useResource = ( { resource, term, exclude, fields } ) => {
	const [ state, setState ] = useState( {
		status: 'IDLE',
		resources: [],
	} );

	useEffect( () => {
		async function fetchData() {
			setState( {
				status: 'LOADING',
				resources: [],
			} );

			const response = await searchResource(
				resource,
				term,
				exclude,
				fields
			);
			setState( {
				status: 'IDLE',
				resources: response,
			} );
		}

		fetchData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ resource, term, JSON.stringify( exclude ) ] );

	return {
		resources: state.status === 'LOADING' ? [] : state.resources,
		isLoading: state.status === 'LOADING',
		reset: () =>
			setState( {
				status: 'IDLE',
				resources: [],
			} ),
	};
};

export default useResource;
