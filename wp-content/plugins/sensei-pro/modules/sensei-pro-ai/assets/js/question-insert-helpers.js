/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { select } from '@wordpress/data';

/**
 * External dependencies
 */
import questionBlock from 'sensei/assets/blocks/quiz/question-block';
import { isQuestionEmpty } from 'sensei/assets/blocks/quiz/data';

/**
 * Internal dependencies
 *
 * @param {Array} questionBlocks Question blocks inside the quiz.
 *
 * @return {number} Next question index.
 */
const getNextQuestionIndex = ( questionBlocks ) => {
	const lastBlock =
		questionBlocks.length && questionBlocks[ questionBlocks.length - 1 ];

	const hasEmptyLastBlock =
		lastBlock && isQuestionEmpty( lastBlock.attributes );

	return hasEmptyLastBlock
		? questionBlocks.length - 1
		: questionBlocks.length;
};

/**
 * Insert a question block.
 *
 * @param {Object}   question              Question object.
 * @param {number}   index                 Index to insert the question.
 * @param {Function} insertBlock           Insert block function.
 * @param {string}   clientId              Client ID of the quiz block.
 * @param {Function} updateBlockAttributes Update block attributes function.
 */
const insertQuestion = (
	question,
	index,
	insertBlock,
	clientId,
	updateBlockAttributes
) => {
	const newQuestionBlock = createBlock( questionBlock.name, {
		title: question.question,
		answer: {
			answers: question.answers.map( ( answer, position ) => {
				return {
					label: answer,
					correct: position === question.correct_answer_index,
				};
			} ),
		},
	} );
	insertBlock( newQuestionBlock, index, clientId, true );
	insertFeedbacks(
		question.correct_answer_explanation,
		question.nudge_towards_correct_answer,
		insertBlock,
		newQuestionBlock.clientId,
		updateBlockAttributes
	);
};

/**
 * Insert feedback blocks for a particular question.
 *
 * @param {string}   correctFeedback       Feedback for correct answer.
 * @param {string}   incorrectFeedback     Feedback for incorrect answer.
 * @param {Function} insertBlock           Insert block function.
 * @param {string}   clientId              Client ID of the question block.
 * @param {Function} updateBlockAttributes Update block attributes function.
 */
const insertFeedbacks = (
	correctFeedback,
	incorrectFeedback,
	insertBlock,
	clientId,
	updateBlockAttributes
) => {
	setTimeout( async () => {
		const questionInnerBlocks = select( 'core/block-editor' ).getBlocks(
			clientId
		);

		questionInnerBlocks.forEach( ( block ) => {
			if (
				[
					'sensei-lms/quiz-question-feedback-correct',
					'sensei-lms/quiz-question-feedback-incorrect',
				].includes( block.name )
			) {
				updateBlockAttributes( block.innerBlocks[ 0 ].clientId, {
					content:
						'sensei-lms/quiz-question-feedback-correct' ===
						block.name
							? correctFeedback
							: incorrectFeedback,
				} );
			}
		} );
	}, 0 );
};

export { getNextQuestionIndex, insertQuestion, insertFeedbacks };
