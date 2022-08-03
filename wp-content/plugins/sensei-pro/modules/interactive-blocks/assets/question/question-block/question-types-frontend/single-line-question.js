/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Single-line question frontend component.
 *
 * @param {Object}   props                Component props.
 * @param {Object}   props.answer         Original block information to elaborate answer options.
 * @param {string[]} props.answer.answers Array with all the accepted answers.
 * @param {boolean}  props.readOnly       Defines if the component should accept input from the user.
 * @param {Object}   props.state          Current question state. Will be null if not available.
 * @param {Function} props.setState       Callback to update the question state so it is persisted.
 */
export const SingleLineQuestion = ( {
	answer: { answers },
	readOnly,
	state,
	setState,
} ) => {
	const currentState = state ?? { expected: answers, actual: '' };

	const onChange = ( event ) => {
		setState( { ...currentState, actual: event.target.value } );
	};

	return (
		<div className="sensei-lms-interactive-block-question__single-line">
			<div className="sensei-lms-interactive-block-question__single-line-input-placeholder">
				<input
					type="text"
					className="sensei-lms-interactive-block-question__single-line-input"
					aria-label={ __( 'Your answer', 'sensei-pro' ) }
					placeholder={ __( 'Add answer', 'sensei-pro' ) }
					value={ currentState.actual }
					readOnly={ readOnly }
					onChange={ onChange }
				/>
			</div>
		</div>
	);
};

/**
 * Checks if the answer for the given question state is correct or not.
 *
 * @param {Object}   state          The state of the question as defined by GapFillQuestion.
 * @param {string[]} state.expected An array with all the accepted values for the gap.
 * @param {string}   state.actual   The actual response.
 */
export const grade = ( state ) => {
	if ( state === null ) {
		return false;
	}
	return state.expected.some(
		( e ) => e.toLowerCase() === state.actual.toLowerCase()
	);
};
