/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Gap-fill question frontend component.
 *
 * @param {Object}   props               Component props.
 * @param {string}   props.id            Unique identifier.
 * @param {Object}   props.answer        Original block information to elaborate answer options.
 * @param {string}   props.answer.before Text before the gap.
 * @param {string[]} props.answer.gap    Array with all the accepted texts for the gap.
 * @param {string}   props.answer.after  Text after the gap.
 * @param {boolean}  props.readOnly      Defines if the component should accept input from the user.
 * @param {Object}   props.state         Current question state. Will be null if not available.
 * @param {Function} props.setState      Callback to update the question state so it is persisted.
 */
export const GapFillQuestion = ( {
	id,
	answer: { before, gap, after },
	readOnly,
	state,
	setState,
} ) => {
	const currentState = state ?? { expected: gap, actual: '' };

	const onChange = ( event ) => {
		setState( { ...currentState, actual: event.target.value } );
	};

	return (
		<div className="sensei-lms-interactive-block-question__gap-fill">
			<ol
				className="sensei-lms-interactive-block-question__gap-fill-list"
				aria-labelledby={ 'sensei-question-title-' + id }
			>
				<li>{ before } </li>
				<li>
					<input
						type="text"
						className="sensei-lms-interactive-block-question__gap-fill-input"
						aria-label={ __(
							'Fill the blank input',
							'sensei-pro'
						) }
						placeholder={ __( 'Your answer', 'sensei-pro' ) }
						value={ currentState.actual }
						onChange={ onChange }
						readOnly={ readOnly }
					/>
				</li>
				<li> { after }</li>
			</ol>
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
