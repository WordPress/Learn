/**
 * WordPress dependencies
 */
import fetch from '@wordpress/api-fetch';

export const create = async ( {
	id,
	title,
	description,
	audience,
	skillLevel,
} ) => {
	return fetch( {
		path: '/sensei-pro-ai/v1/course-outline',
		method: 'POST',
		data: {
			course_id: id,
			course_title: title,
			course_description: description,
			intended_audience: audience,
			skill_level: skillLevel,
		},
	} );
};
