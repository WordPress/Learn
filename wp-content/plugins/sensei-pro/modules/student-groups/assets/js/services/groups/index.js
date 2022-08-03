/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

export const create = async ( name ) =>
	apiFetch( {
		path: '/sensei-pro-student-groups/v1/groups',
		method: 'POST',
		data: { name },
	} );
