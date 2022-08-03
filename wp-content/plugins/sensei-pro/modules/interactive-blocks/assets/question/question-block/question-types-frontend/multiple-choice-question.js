/**
 * External dependencies
 */
import { xor } from 'lodash';

/**
 * Sensei dependencies.
 */
import { InputToggle } from 'sensei/assets/blocks/quiz/answer-blocks/option-toggle';

/**
 * Multiple Choice question frontend component.
 *
 * @param {Object}   props                Component props.
 * @param {string}   props.id             Unique identifier.
 * @param {Object}   props.answer         Original block information to elaborate answer options.
 * @param {Object[]} props.answer.answers Array with the different answer options and their correctness.
 * @param {boolean}  props.readOnly       Defines if the component should accept input from the user.
 * @param {Object}   props.state          Current question state. Will be null if not available.
 * @param {Function} props.setState       Callback to update the question state so it is persisted.
 */
export const MultipleChoiceQuestion = ( {
	id,
	answer: { answers },
	readOnly,
	state,
	setState,
} ) => {
	const isCheckbox = answers.filter( ( e ) => e.correct ).length !== 1;

	// Creates initial state from the original answers information.
	const initialiseState = () => {
		return {
			correct: answers.reduce( ( result, answer, index ) => {
				if ( answer.correct ) {
					result.push( index );
				}
				return result;
			}, [] ),
			checked: [],
		};
	};

	const currentState = state ?? initialiseState();

	// Updates checked status for the given option.
	const updateCheckedStatus = ( option ) => () => {
		const newState = { ...currentState };
		if ( isCheckbox ) {
			// For checkbox we just toggle the value for the current option.
			newState.checked = xor( newState.checked, [ option ] );
		} else {
			newState.checked = [ option ];
		}
		setState( newState );
	};

	return (
		<div className="sensei-lms-interactive-block-question__multiple-choice">
			<ol
				aria-labelledby={ 'sensei-question-title-' + id }
				className="sensei-lms-interactive-block-question__multiple-choice-list"
			>
				{ answers.map( ( { label }, index ) => {
					const elementId = `sensei-question-answers-${ id }-${ index }`;
					const isChecked = currentState.checked.includes( index );
					return (
						<li
							key={ index }
							className="sensei-lms-interactive-block-question__multiple-choice-option"
						>
							<InputToggle
								id={ elementId }
								name={ 'sensei-question-answers-' + id }
								type={ isCheckbox ? 'checkbox' : 'radio' }
								checked={ isChecked }
								onChange={ updateCheckedStatus( index ) }
								disabled={ readOnly }
							/>
							<label htmlFor={ elementId }>{ label }</label>
						</li>
					);
				} ) }
			</ol>
		</div>
	);
};

/**
 * Checks if the answer for the given question state is correct or not.
 *
 * @param {Object}   state         The state of the question as defined by MultipleChoiceQuestion.
 * @param {number[]} state.correct List of indexes for correct answers.
 * @param {number[]} state.checked List of indexes for checked answers.
 */
export const grade = ( state ) => {
	if ( state === null ) {
		return false;
	}
	return (
		JSON.stringify( state.correct.sort() ) ===
		JSON.stringify( state.checked.sort() )
	);
};
