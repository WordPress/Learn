/**
 * External dependencies
 */
import orderingQuestionType from 'shared-module/ordering-question/ordering-question-type';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import OrderingSubtitle from './subtitle/ordering-subtitle';

/**
 * @typedef QuestionType
 *
 * @property {string}   title       Question type name.
 * @property {string}   description Question type description.
 * @property {Function} edit        Editor component.
 * @property {Function} validate    Validation callback.
 * @property {Object}   messages    Message strings.
 */
/**
 * Question type definitions.
 *
 * @type {Object.<string, QuestionType>}
 */
export const orderingQuestionTypes = {
	ordering: {
		...orderingQuestionType,
		subtitle: OrderingSubtitle,
		settings: [],
		feedback: true,
		validate: ( { answers = [] } = {} ) => {
			return {
				noAnswers: answers.filter( ( a ) => a.label ).length < 2,
			};
		},
		messages: {
			noAnswers: __( 'Add at least two answers', 'sensei-pro' ),
		},
	},
};

function addOrderingQuestionType( existingQuestionTypes ) {
	return {
		...existingQuestionTypes,
		...orderingQuestionTypes,
	};
}

addFilter(
	'sensei-lms.Question.questionTypes',
	'sensei-pro/ordering-question-type',
	addOrderingQuestionType
);
