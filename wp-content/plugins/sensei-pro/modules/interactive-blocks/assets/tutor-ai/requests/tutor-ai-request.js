/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch questions from api.
 *
 * @param {string} question
 * @param {string} answer
 * @param {string} context
 * @param {string} studentReply
 * @return {Promise<unknown>} Promise with the response.
 */
export const fetchTutorResponse = async (
	question,
	answer,
	context,
	studentReply
) =>
	apiFetch( {
		method: 'POST',
		path: `/sensei-pro-interactive-blocks/v1/tutor-chat`,
		parse: false,
		data: {
			question,
			answer,
			context,
			student_reply: studentReply,
		},
	} );
