/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { fetchQuestions } from '../../requests/fetch-questions';
import {
	getNextQuestionIndex,
	insertQuestion,
} from '../../question-insert-helpers';

/**
 * Response for useGPTQuestionGenerator.
 *
 * @typedef useGPTQuestionGeneratorResponse
 *
 * @property {boolean}  isBusy                Indicate if request is in progress.
 * @property {Function} generateQuizQuestions Method to set the question text and count to generate.
 * @property {number}   hasQuestions          If the quiz has questions already.
 */

/**
 * Hook for creating the question in the quiz block.
 *
 * @return {useGPTQuestionGeneratorResponse} Indicator flags and functions for generating questions.
 */
export const useGPTQuestionGenerator = () => {
	const { insertBlock, updateBlockAttributes } = useDispatch(
		'core/block-editor'
	);

	const [ isBusy, setIsBusy ] = useState( false );
	const [ questionText, setQuestionText ] = useState( null );
	const [ questionCount, setQuestionCount ] = useState( 3 );
	const { createErrorNotice } = useDispatch( 'core/notices' );
	const [ hasQuestions, setHasQuestions ] = useState( false );
	const [ limitErrorMessage, setLimitErrorMessage ] = useState( '' );
	const [ disabledByTimeout, setDisabledByTimeout ] = useState( false );

	const quizBlock = useSelect( ( select ) => {
		const lessonQuizBlockId = select( 'sensei/quiz-structure' ).getBlock();
		return select( 'core/block-editor' ).getBlock( lessonQuizBlockId );
	}, [] );

	useEffect( () => {
		if ( quizBlock ) {
			const nextQuestionIndex = getNextQuestionIndex(
				quizBlock.innerBlocks
			);
			if ( nextQuestionIndex > 0 !== hasQuestions ) {
				setHasQuestions( ! hasQuestions );
			}
		}
	} );

	useEffect( () => {
		const generateQuestions = async () => {
			setIsBusy( true );

			try {
				let gptResponse = await fetchQuestions(
					questionText,
					questionCount
				);

				gptResponse = await gptResponse.json();

				if ( ! gptResponse || ! gptResponse.questions ) {
					throw new Error(
						__( 'Unexpected error. Try again later.', 'sensei-pro' )
					);
				}

				const questionJson = gptResponse.questions;

				let index = getNextQuestionIndex( quizBlock.innerBlocks );
				if ( questionJson ) {
					questionJson.forEach( ( question ) => {
						insertQuestion(
							question,
							index++,
							insertBlock,
							quizBlock.clientId,
							updateBlockAttributes
						);
					} );
				}
			} catch ( error ) {
				handleError(
					error,
					createErrorNotice,
					setLimitErrorMessage,
					setDisabledByTimeout
				);
			} finally {
				setIsBusy( false );
				setQuestionText( null );
			}
		};

		if ( questionText && questionCount ) {
			generateQuestions();
		}
	}, [ questionText, questionCount ] );

	const generateQuizQuestions = ( text, count ) => {
		setQuestionText( text );
		setQuestionCount( count );
	};

	return {
		isBusy,
		generateQuizQuestions,
		limitErrorMessage,
		disabledByTimeout,
		hasQuestions,
	};
};

const handleError = async (
	error,
	createErrorNotice,
	setLimitErrorMessage,
	setDisabledByTimeout
) => {
	if ( error.status && error.status === 429 ) {
		setLimitErrorMessage( await error.json() );
		setDisabledByTimeout( true );

		// Disable the button for 5 minutes so that the
		// user can't hit the API again immediately.
		setTimeout( () => {
			setDisabledByTimeout( false );
		}, 1000 * 60 * 5 );

		return;
	}

	const defaultErrorMessage = __(
		'Sorry could not generate questions',
		'sensei-pro'
	);

	let errorMessage = null;

	if ( error instanceof Error ) {
		errorMessage = error.message;
	} else if ( error instanceof Response ) {
		errorMessage = await error.json();
	}

	if ( errorMessage && errorMessage.message ) {
		errorMessage = errorMessage.message;
	}

	createErrorNotice( errorMessage || defaultErrorMessage, {
		type: 'snackbar',
	} );
};
