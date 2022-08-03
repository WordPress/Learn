/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

export const getCourses = async ( groupId ) =>
	apiFetch( {
		method: 'GET',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/courses`,
	} );

export const create = async ( groupId, course ) =>
	apiFetch( {
		method: 'POST',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/courses`,
		data: {
			courseId: course.id,
			...course.accessPeriod,
		},
	} );

export const remove = async ( groupId, course, removeEnrolments ) =>
	apiFetch( {
		method: 'DELETE',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/courses/${
			course.id
		}?remove_enrolments=${ removeEnrolments ? 1 : 0 }`,
	} );

export const update = async ( groupId, course ) => {
	const response = await apiFetch( {
		method: 'PATCH',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/courses/${ course.id }`,
		data: {
			...course.accessPeriod,
		},
	} );

	return {
		id: response.id,
		title: response.title,
		accessPeriod: {
			startDate: response.startDate,
			endDate: response.endDate,
			status: response.status,
		},
	};
};
