/**
 * Internal dependencies
 */
import {
	BooleanQuestion,
	grade as gradeBooleanQuestion,
} from './boolean-question';
import {
	MultipleChoiceQuestion,
	grade as gradeMultipleChoiceQuestion,
} from './multiple-choice-question';
import {
	GapFillQuestion,
	grade as gradeGapFillQuestion,
} from './gap-fill-question';
import {
	OrderingQuestion,
	grade as gradeOrderingQuestion,
} from './ordering-question';
import {
	SingleLineQuestion,
	grade as gradeSingleLineQuestion,
} from './single-line-question';

const questionTypes = {
	'multiple-choice': {
		component: MultipleChoiceQuestion,
		grade: gradeMultipleChoiceQuestion,
	},
	boolean: {
		component: BooleanQuestion,
		grade: gradeBooleanQuestion,
	},
	'gap-fill': {
		component: GapFillQuestion,
		grade: gradeGapFillQuestion,
	},
	'single-line': {
		component: SingleLineQuestion,
		grade: gradeSingleLineQuestion,
	},
	ordering: {
		component: OrderingQuestion,
		grade: gradeOrderingQuestion,
	},
};

export default questionTypes;
