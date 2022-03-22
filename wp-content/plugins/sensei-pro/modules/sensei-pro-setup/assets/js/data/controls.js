/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies.
 */
import { REST_API_BASE_PATH, FETCH_FROM_API } from './constants';

export default {
	[ FETCH_FROM_API ]: ( { request } ) =>
		apiFetch( {
			...request,
			path: `${ REST_API_BASE_PATH }${ request.path }`,
		} ),
};
