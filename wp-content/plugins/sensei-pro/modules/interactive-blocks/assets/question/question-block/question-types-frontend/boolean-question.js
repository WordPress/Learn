/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	MultipleChoiceQuestion,
	grade as MultipleChoiceQuestionGrade,
} from './multiple-choice-question';

/**
 * Boolean question frontend component.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.id       Unique identifier.
 * @param {Object}   props.answer   Original block information to elaborate answer options.
 * @param {boolean}  props.readOnly Defines if the component should accept input from the user.
 * @param {Object}   props.state    Current question state. Will be null if not available.
 * @param {Function} props.setState Callback to update the question state so it is persisted.
 */
export const BooleanQuestion = ( {
	id,
	answer,
	readOnly,
	state,
	setState,
} ) => {
	// By default answer is undefined. Default behaviour must be considering it as true.
	const expectedAnswer = answer?.correct ?? true;

	const answerForMultipleChoice = {
		answers: [
			{
				label: __( 'True', 'sensei-pro' ),
				correct: expectedAnswer === true,
			},
			{
				label: __( 'False', 'sensei-pro' ),
				correct: expectedAnswer === false,
			},
		],
	};

	return (
		<MultipleChoiceQuestion
			id={ id }
			answer={ answerForMultipleChoice }
			readOnly={ readOnly }
			state={ state }
			setState={ setState }
		/>
	);
};

export const grade = MultipleChoiceQuestionGrade;
