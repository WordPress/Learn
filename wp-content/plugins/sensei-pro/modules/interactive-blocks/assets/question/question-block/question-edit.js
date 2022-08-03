/**
 * External dependencies
 */
import classnames from 'classnames';
import { QuestionTypeToolbar } from 'sensei/assets/blocks/quiz/question-block/question-type-toolbar';
import { QuestionContext } from 'sensei/assets/blocks/quiz/question-block/question-context';
import { useHasSelected } from 'sensei/assets/shared/helpers/blocks';
import SingleLineInput from 'sensei/assets/shared/blocks/single-line-input';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import questionTypes, { questionOptions } from './question-types-editor';
import useNoEmptyAnswers from './use-no-empty-answers';
import withRecursionNotAllowed from '../../with-recursion-not-allowed';
import { CompletedStatus } from '../../shared/supports-required/elements';

/**
 * React hook that offers some helpful functions to support block navigation.
 *
 * @param {string} clientId Block client ID.
 *
 * @return {Object} Object with the helper functions.
 */
const useBlockNavigation = ( clientId ) => {
	const { removeBlock, selectBlock } = useDispatch( 'core/block-editor' );
	const { questionInnerBlocks } = useSelect( ( select ) => ( {
		questionInnerBlocks: select( 'core/block-editor' ).getBlocks(
			clientId
		),
	} ) );

	const selectDescription = () => {
		const descriptionBlock = questionInnerBlocks[ 0 ]?.clientId;
		const firstDescriptionBlock =
			questionInnerBlocks[ 0 ]?.innerBlocks?.[ 0 ]?.clientId;

		if ( firstDescriptionBlock ) {
			selectBlock( firstDescriptionBlock );
		} else if ( descriptionBlock ) {
			selectBlock( descriptionBlock );
		}
	};

	return { removeBlock, selectDescription };
};

/**
 * Question block edit component.
 *
 * @param {Object}   props                  Component props.
 * @param {Object}   props.attributes       Block attributes.
 * @param {string}   props.attributes.title Title attribute.
 * @param {string}   props.attributes.type  Question type.
 * @param {Function} props.setAttributes    Block setAttributes function.
 */
const QuestionEdit = ( props ) => {
	const {
		clientId,
		attributes: { title, type, answer = {}, required: blockIsRequired },
		setAttributes,
	} = props;

	useNoEmptyAnswers( type, answer, setAttributes );

	const blockProps = useBlockProps();

	// Set up question context.
	const [ showAnswerFeedback, toggleAnswerFeedback ] = useState( false );
	const questionContext = {
		AnswerBlock: type && questionTypes[ type ],
		hasSelected: useHasSelected( props ),
		setAttributes,
		answer,
		canHaveFeedback: true,
		answerFeedback: {
			showAnswerFeedback,
			toggleAnswerFeedback,
		},
	};

	const { removeBlock, selectDescription } = useBlockNavigation( clientId );

	return (
		<div { ...blockProps }>
			<div
				className={ classnames( 'sensei-lms-question-block', {
					'show-answer-feedback': showAnswerFeedback,
				} ) }
			>
				<h3 className="sensei-lms-interactive-block-question__title">
					{ blockIsRequired && (
						<CompletedStatus
							className="sensei-lms-interactive-block-question__completed-status"
							showTooltip={ false }
						/>
					) }
					<SingleLineInput
						placeholder={ __( 'Question title', 'sensei-pro' ) }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						onEnter={ selectDescription }
						onRemove={ () => removeBlock( clientId ) }
					/>
				</h3>
				<QuestionContext.Provider value={ questionContext }>
					<InnerBlocks
						template={ [
							[
								'sensei-pro/question-description',
								{ slot: 'description' },
							],
							[
								'sensei-pro/question-answers',
								{ slot: 'question' },
							],
							[
								'sensei-pro/question-answer-feedback-correct',
								{ slot: 'feedback_correct' },
							],
							[
								'sensei-pro/question-answer-feedback-incorrect',
								{ slot: 'feedback_incorrect' },
							],
						] }
						templateLock={ true }
					/>
				</QuestionContext.Provider>
				<div className="wp-block-button sensei-lms-interactive-block-question__submit-button">
					<button
						type="button"
						className="wp-block-button__link"
						onClick={ ( e ) => e.preventDefault() }
					>
						{ __( 'Submit', 'sensei-pro' ) }
					</button>
				</div>
				<BlockControls>
					<>
						<QuestionTypeToolbar
							value={ type }
							onSelect={ ( nextValue ) =>
								setAttributes( { type: nextValue, answer: {} } )
							}
							options={ questionOptions }
						/>
					</>
				</BlockControls>
			</div>
		</div>
	);
};

export default withRecursionNotAllowed( QuestionEdit );
