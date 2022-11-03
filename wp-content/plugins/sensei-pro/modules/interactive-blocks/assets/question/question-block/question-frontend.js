/**
 * WordPress dependencies
 */
import { useState, useRef, useEffect } from '@wordpress/element';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { isEmpty, isEqual } from 'lodash';

/**
 * Internal dependencies
 */
import { registerBlockFrontend } from '../../shared/block-frontend';
import AnswerFeedback from '../answer-feedback-block/answer-feedback-frontend';
import questionTypes from './question-types-frontend';
import { BlockSlot } from '../../shared/block-frontend/block-slot';
import { createStateGetter, saveState } from '../../shared/blockState';
import { CompletedStatus } from '../../shared/supports-required/elements';

/**
 * Question component.
 *
 * @param {Object} props               Component props.
 * @param {Object} props.clientId      Unique identifier re-generated on every page reload.
 * @param {Object} props.attributes    Question attributes.
 * @param {Object} props.setAttributes Updates the question attributes.
 * @param {Object} props.blockProps    Block props.
 * @param {Object} props.innerBlocks   Inner blocks.
 */
const Question = ( {
	clientId,
	attributes,
	setAttributes,
	blockProps,
	innerBlocks,
} ) => {
	const blockId = attributes.blockId;
	const type = attributes.type;
	const answer = attributes.answer;

	// States.
	const [ stateLoaded, setStateLoaded ] = useState( false );
	const [ success, setSuccess ] = useState( null );
	const [ submitted, setSubmitted ] = useState( null );
	const [ questionState, setQuestionState ] = useState( null );

	// Load persisted state.
	useEffect( () => {
		async function loadData() {
			const getState = createStateGetter();
			const persistedState = ( await getState( [ blockId ] ) )[ blockId ];
			// We validate that the state is for the same type and for the same set of answer options.
			// This is to ensure that state is reset when question type or answers change.
			const isValidState =
				! isEmpty( persistedState ) &&
				persistedState.type === type &&
				isEqual( persistedState.answer, answer );
			if ( isValidState ) {
				setSubmitted( persistedState.submitted );
				setSuccess( persistedState.success );
				setQuestionState( persistedState.questionState );
			}
			setStateLoaded( true );
		}
		loadData();
	}, [] );

	// Update the completed state
	useEffect( () => {
		setAttributes( { completed: success } );
	}, [ success, setAttributes ] );

	const QuestionType = questionTypes[ attributes.type ];

	const reset = () => {
		// Set submitted and success.
		setSubmitted( false );
		setSuccess( null );
		// Save whole state.
		saveState( {
			[ blockId ]: {
				submitted: false,
				success: null,
				questionState,
				type,
			},
		} );
	};

	const onSubmit = ( e ) => {
		// Prevent form submission.
		e.preventDefault();
		// Check grade result.
		const result = QuestionType.grade( questionState );
		// Set submitted and success.
		setSubmitted( true );
		setSuccess( result );
		// Save whole state.
		saveState( {
			[ blockId ]: {
				submitted: true,
				success: result,
				questionState,
				type,
				answer,
			},
		} );
	};

	return (
		<BlockSlot.Provider value={ innerBlocks }>
			<div { ...blockProps }>
				<form onSubmit={ onSubmit }>
					<fieldset>
						<legend
							id={ 'sensei-question-title-' + clientId }
							className="screen-reader-text"
						>
							{ attributes.title }
						</legend>
						<h3
							className="sensei-lms-interactive-block-question__title"
							aria-hidden={ true }
						>
							{ attributes.required && (
								<CompletedStatus
									message={ __(
										'Required - Answer this question correctly to complete.',
										'sensei-pro'
									) }
									className="sensei-lms-interactive-block-question__completed-status"
									completed={ attributes.completed }
								/>
							) }
							{ attributes.title }
						</h3>
						<div className="sensei-lms-interactive-block-question__description">
							<BlockSlot name="description" />
						</div>
						<div>
							{ stateLoaded && (
								<QuestionType.component
									id={ clientId }
									answer={ answer }
									readOnly={ submitted }
									state={ questionState }
									setState={ setQuestionState }
								/>
							) }
							{ ! submitted && (
								<QuestionSubmit
									questionTitle={ attributes.title }
								/>
							) }
						</div>
						<div role="alert">
							{ submitted && (
								<>
									<BlockSlot
										name={
											success
												? 'feedback_correct'
												: 'feedback_incorrect'
										}
									/>
									<QuestionReset
										questionTitle={ attributes.title }
										reset={ reset }
									/>
								</>
							) }
						</div>
					</fieldset>
				</form>
			</div>
		</BlockSlot.Provider>
	);
};

/**
 * Question submit button component.
 *
 * @param {Object} props               Component props.
 * @param {string} props.questionTitle The question title.
 */
const QuestionSubmit = ( { questionTitle } ) => {
	const buttonLabel = sprintf(
		/* translators: Question title. */
		__( 'Submit answer: %s', 'sensei-lms' ),
		questionTitle
	);

	return (
		<div className="wp-block-button sensei-lms-interactive-block-question__submit-button">
			<button
				type="submit"
				className="wp-block-button__link"
				aria-label={ buttonLabel }
			>
				{ __( 'Submit', 'sensei-pro' ) }
			</button>
		</div>
	);
};

/**
 * Question reset button component.
 *
 * @param {Object}   props               Component props.
 * @param {string}   props.questionTitle The quesiton title.
 * @param {Function} props.reset         Callback to reset the question status.
 */
const QuestionReset = ( { questionTitle, reset } ) => {
	const button = useRef( null );

	const buttonLabel = sprintf(
		/* translators: Question title. */
		__( 'Reset: %s', 'sensei-pro' ),
		questionTitle
	);

	const onClick = () => {
		reset();

		// Focus on the first input of the form.
		const firstInput = button.current
			.closest( 'form' )
			.querySelector( 'input' );

		setTimeout( () => {
			firstInput.focus();
		}, 1 );
	};

	return (
		<div className="wp-block-button sensei-lms-interactive-block-question__reset-button">
			<button
				ref={ button }
				type="button"
				className="wp-block-button__link"
				aria-label={ buttonLabel }
				onClick={ onClick }
			>
				{ __( 'Reset', 'sensei-pro' ) }
			</button>
		</div>
	);
};

registerBlockFrontend( {
	name: 'sensei-pro/question',
	run: Question,
} );

registerBlockFrontend( {
	name: 'sensei-pro/question-answer-feedback-correct',
	run: ( props ) => <AnswerFeedback correct={ true } { ...props } />,
} );

registerBlockFrontend( {
	name: 'sensei-pro/question-answer-feedback-incorrect',
	run: ( props ) => <AnswerFeedback correct={ false } { ...props } />,
} );
