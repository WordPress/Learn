/**
 * External dependencies
 */
import { unfilteredQuestionTypes } from 'sensei/assets/blocks/quiz/answer-blocks';
import orderingQuestionType from 'shared-module/ordering-question/ordering-question-type';

/**
 * Internal dependencies
 */
import { SingleLineQuestionEdit } from './single-line-edit';

const questionTypes = {
	'multiple-choice': unfilteredQuestionTypes[ 'multiple-choice' ],
	boolean: unfilteredQuestionTypes.boolean,
	'gap-fill': unfilteredQuestionTypes[ 'gap-fill' ],
	'single-line': {
		...unfilteredQuestionTypes[ 'single-line' ],
		edit: SingleLineQuestionEdit,
	},
	ordering: orderingQuestionType,
};

export const questionOptions = Object.entries( questionTypes ).map(
	( [ value, settings ] ) => ( {
		...settings,
		label: settings.title,
		value,
	} )
);

export default questionTypes;
