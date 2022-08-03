/**
 * External dependencies
 */
import { shuffle } from 'lodash';
import Question from 'shared-module/ordering-question/frontend/Question';

/**
 * Ordering question frontend component.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.id       Unique identifier.
 * @param {Object}   props.answer   Original block information to elaborate answer options.
 * @param {boolean}  props.readOnly Defines if the component should accept input from the user.
 * @param {Object}   props.state    Current question state. Will be null if not available.
 * @param {Function} props.setState Callback to update the question state so it is persisted.
 */
export const OrderingQuestion = ( {
	id,
	answer,
	readOnly,
	state,
	setState,
} ) => {
	const initialiseState = () => {
		return {
			answers: shuffle(
				answer.answers.map( ( item, index ) => ( {
					...item,
					id: index.toString(),
				} ) )
			),
		};
	};
	const currentState = state ?? initialiseState();

	const onChange = ( newOrder ) => {
		setState( {
			answers: currentState.answers.sort(
				( a, b ) => newOrder.indexOf( a.id ) - newOrder.indexOf( b.id )
			),
		} );
	};

	return (
		<Question
			id={ id }
			disabled={ readOnly }
			answers={ currentState.answers }
			onChange={ onChange }
		/>
	);
};

/**
 * Checks if the answer for the given question state is correct or not.
 *
 * @param {Object}   state         The state of the question as defined by OrderingQuestion.
 * @param {Object[]} state.answers List of options and their correct position as an "id" field.
 */
export const grade = ( state ) => {
	return state.answers.every( ( element, i ) => i.toString() === element.id );
};
