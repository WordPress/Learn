/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { createStateGetter, saveState } from '../../shared/blockState';

/**
 * External dependencies
 */
import { isEmpty } from 'lodash';
import { fetchTutorResponse } from '../requests/tutor-ai-request';

const useTutorChatHandler = ( blockId, attributes ) => {
	// Used for loading state from local storage.
	const [ stateLoaded, setStateLoaded ] = useState( false );

	const [ chatMessages, setChatMessages ] = useState( [] );

	const [ isUserLoggedIn, setIsUserLoggedIn ] = useState( false );

	const defaultErrorText = __(
		'Something wrong, please try again',
		'sensei-pro'
	);

	// Indicates if the user has got the correct answer already. Block is set to completed if true.
	// Also used to prevent the user from sending more messages after getting the correct answer.
	const [ gotCorrectAnswer, setGotCorrectAnswer ] = useState( false );

	const [ completed, setCompleted ] = useState( false );

	// Indicates if a request is under process, rendering the loader and input field depends on it.
	const [ isAiBusy, setIsAiBusy ] = useState( false );

	const [ errorMessage, setErrorMessage ] = useState( '' );

	useEffect( () => {
		setIsUserLoggedIn( !! window.senseiProIsUserLoggedIn );
	}, [ window.senseiProIsUserLoggedIn ] );

	// Load state (message history and completion state) from local storage.
	useEffect( () => {
		async function loadData() {
			const getState = createStateGetter();
			const persistedState = ( await getState( [ blockId ] ) )[ blockId ];

			const isValidState =
				! isEmpty( persistedState ) &&
				Object.keys( persistedState ).length === 2 &&
				Array.isArray( persistedState.chatMessages ) &&
				typeof persistedState.gotCorrectAnswer === 'boolean';

			if ( isValidState ) {
				setChatMessages( persistedState.chatMessages );
				setGotCorrectAnswer( persistedState.gotCorrectAnswer );
				setCompleted( persistedState.gotCorrectAnswer );
			}
			setStateLoaded( true );
		}
		loadData();
	}, [] );

	// Send message to tutor AI and insert the response to the chat.
	const fetchTutorAiResponse = async () => {
		try {
			setIsAiBusy( true );

			const response = await fetchTutorResponse(
				attributes.question,
				attributes.correctAnswer,
				attributes.reason,
				chatMessages[ chatMessages.length - 1 ].message
			);
			const responseJson = await response.json();

			if ( ! responseJson ) {
				throw new Error( defaultErrorText );
			}

			await insertMessage( {
				message: responseJson.tutor_response,
				author: 'tutor',
				isCorrect: responseJson.student_correct,
			} );
		} catch ( error ) {
			let errorText = await error.json();

			if ( typeof errorText !== 'string' ) {
				errorText = defaultErrorText;
			}

			setErrorMessage( errorText );
		} finally {
			setIsAiBusy( false );
		}
	};

	// Fetch tutor AI response when the student sends a message.
	useEffect( () => {
		if (
			chatMessages.length > 0 &&
			chatMessages[ chatMessages.length - 1 ].author === 'student'
		) {
			fetchTutorAiResponse();
		}
	}, [ chatMessages ] );

	// Insert a message to the chat.
	const insertMessage = async ( {
		message,
		author = 'tutor',
		isCorrect = false,
	} ) => {
		const isLimitReached =
			chatMessages &&
			chatMessages.filter( ( item ) => 'student' === item.author )
				.length >= attributes.limit;

		// If limit is reached, give the correct answer and set the block to completed.
		if ( 'tutor' === author && ! isCorrect && isLimitReached ) {
			isCorrect = true;
			message =
				__( 'The correct answer is: ', 'sensei-pro' ) +
				attributes.correctAnswer;
		}

		if ( isCorrect ) {
			setCompleted( true );
			setGotCorrectAnswer( true );
		}

		const newMessages = [
			...chatMessages,
			{
				message,
				author,
				isCorrect,
			},
		];

		setChatMessages( newMessages );

		// Save updated chat history to local storage.
		saveState( {
			[ blockId ]: {
				chatMessages: newMessages,
				gotCorrectAnswer: gotCorrectAnswer || isCorrect,
			},
		} );
	};

	return {
		stateLoaded,
		chatMessages,
		gotCorrectAnswer,
		completed,
		isAiBusy,
		isUserLoggedIn,
		insertMessage,
		errorMessage,
		setErrorMessage,
	};
};

export default useTutorChatHandler;
