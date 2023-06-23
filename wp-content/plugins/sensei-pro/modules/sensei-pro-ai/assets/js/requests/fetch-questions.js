/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Fetch questions from api.
 *
 * @param {string} text  Question text.
 * @param {number} count Number of questions to generate.
 *
 * @return {Promise<unknown>} Promise with the response.
 */
export const fetchQuestions = async ( text, count ) =>
	apiFetch( {
		method: 'POST',
		path: `/sensei-pro-ai/v1/gpt/questions`,
		parse: false,
		data: {
			text,
			count,
		},
	} );
