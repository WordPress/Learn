/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';

const searchResource = async (
	resource,
	term = '',
	exclude = [],
	fields = [],
	roles = [],
	include = []
) => {
	const params = new URLSearchParams( {
		per_page: 100,
		search: term,
		_fields: fields.join( ',' ),
		exclude: exclude.join( ',' ),
		roles: roles.join( ',' ),
		include: include.join( ',' ),
	} );

	return apiFetch( {
		path: `/wp/v2/${ resource }?` + params.toString(),
	} );
};

const useResource = ( {
	resource,
	term,
	exclude,
	fields,
	roles = [],
	include = [],
} ) => {
	const [ state, setState ] = useState( {
		isLoading: false,
		resources: [],
		reset: () =>
			setState( {
				status: false,
				resources: [],
			} ),
	} );

	useEffect( () => {
		async function fetchData() {
			setState( {
				isLoading: true,
				resources: [],
			} );

			const response = await searchResource(
				resource,
				term,
				exclude,
				fields,
				roles,
				include
			);
			setState( {
				status: false,
				resources: response,
			} );
		}

		fetchData();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [
		resource,
		term,
		JSON.stringify( exclude ),
		JSON.stringify( include ),
	] );

	return state;
};

export default useResource;
