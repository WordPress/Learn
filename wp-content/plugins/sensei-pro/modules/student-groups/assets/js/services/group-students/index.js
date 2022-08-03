/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

export const removeAll = async ( groupId, studentIds, removeEnrolments ) =>
	apiFetch( {
		method: 'DELETE',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/students?remove_enrolments=${
			removeEnrolments ? 1 : 0
		}`,
		data: {
			student_ids: studentIds,
		},
	} );

export const addAll = async ( groupId, studentIds ) =>
	apiFetch( {
		method: 'POST',
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/students`,
		data: {
			student_ids: studentIds,
		},
	} );

export const remove = async ( groupId, studentId, removeEnrolments ) =>
	await apiFetch( {
		path: `/sensei-pro-student-groups/v1/groups/${ groupId }/students/${ studentId }?remove_enrolments=${
			removeEnrolments ? 1 : 0
		}`,

		method: 'DELETE',
	} );
