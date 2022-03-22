/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import OrderingAnswer from './answer-blocks/ordering';
import OrderingSubtitle from './subtitle/ordering-subtitle';

/**
 * @typedef QuestionType
 *
 * @property {string}   title       Question type name.
 * @property {string}   description Question type description.
 * @property {Function} edit        Editor component.
 * @property {Function} validate    Validation callback.
 * @property {Object}   messages    Message string.s
 */
/**
 * Question type definitions.
 *
 * @type {Object.<string, QuestionType>}
 */
const questionTypes = {
	ordering: {
		title: __( 'Ordering', 'sensei-pro' ),
		description: __(
			'Place the answers in the correct order.',
			'sensei-pro'
		),
		edit: OrderingAnswer,
		view: OrderingAnswer.view,
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
		...questionTypes,
	};
}

addFilter(
	'sensei-lms.Question.questionTypes',
	'sensei-pro/ordering-question-type',
	addOrderingQuestionType
);
