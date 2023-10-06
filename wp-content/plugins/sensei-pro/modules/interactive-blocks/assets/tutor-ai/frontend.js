/**
 * Internal dependencies
 */
import { BlockFrontend, registerBlockFrontend } from '../shared/block-frontend';
import FrontendStudentResponse from './student-text-block/student-text-frontend';
import FrontendAiAnswer from './answer-block/answer-frontend';
import useTutorChatHandler from './hooks/use-tutor-chat-handler';

/**
 * WordPress dependencies
 */
import { cloneBlock } from '@wordpress/blocks';
import { createContext, useEffect, useRef } from '@wordpress/element';
import { Notice } from '@wordpress/components';

export const TutorAIContext = createContext( {} );

registerBlockFrontend( {
	name: 'sensei-pro/ai-answer',
	run: FrontendAiAnswer,
} );

registerBlockFrontend( {
	name: 'sensei-pro/tutor-ai',
	run: FrontendTutorAi,
} );

registerBlockFrontend( {
	name: 'sensei-pro/ai-student-response',
	run: FrontendStudentResponse,
} );

export function FrontendTutorAi( props ) {
	const { innerBlocks, attributes, setAttributes } = props;
	const blockId = attributes.blockId;

	const {
		stateLoaded,
		chatMessages,
		gotCorrectAnswer,
		completed,
		isAiBusy,
		isUserLoggedIn,
		insertMessage,
		errorMessage,
		setErrorMessage,
	} = useTutorChatHandler( blockId, attributes );

	useEffect( () => {
		setAttributes( { completed } );
	}, [ completed ] );

	const inputRef = useRef();
	const tutorAiRef = useRef();
	const chatMessagesRef = useRef();

	const headerBlock = innerBlocks[ 0 ], // Header block
		questionBlock = innerBlocks[ 1 ], // Question block
		answerBlock = innerBlocks[ 2 ]; // Answer block;

	const scrollToBottom = () => {
		if ( chatMessagesRef.current ) {
			chatMessagesRef.current.scrollTop =
				chatMessagesRef.current.scrollHeight;
		}
	};

	useEffect( () => {
		setTimeout( () => {
			scrollToBottom();
			inputRef?.current?.focus();
		} );
	}, [ chatMessages ] );

	useEffect( () => {
		if ( tutorAiRef?.current?.parentNode ) {
			tutorAiRef.current.parentNode.style.display =
				! isUserLoggedIn ||
				! attributes.question ||
				! attributes.correctAnswer
					? 'none'
					: '';
		}
	}, [
		tutorAiRef,
		isUserLoggedIn,
		attributes.correctAnswer,
		attributes.question,
	] );

	return (
		<section ref={ tutorAiRef }>
			{ isUserLoggedIn &&
			attributes.question &&
			attributes.correctAnswer ? (
				<TutorAIContext.Provider
					value={ {
						gotCorrectAnswer,
						chatMessages,
						insertMessage,
						inputRef,
						isAiBusy,
					} }
				>
					<div className="sensei-pro-tutor-ai__top">
						<BlockFrontend
							key={ headerBlock.clientId }
							block={ headerBlock }
						/>
						{ errorMessage && (
							<Notice
								className="sensei-pro-tutor-ai__notice"
								dismissible={ true }
								onDismiss={ () => {
									setErrorMessage( '' );
								} }
								status="warning"
							>
								{ errorMessage }
							</Notice>
						) }
					</div>
					<div
						className="sensei-pro-tutor-ai__chat"
						ref={ chatMessagesRef }
					>
						{ stateLoaded &&
							chatMessages.map( ( message ) => {
								const targetBlock = cloneBlock(
									message.author === 'tutor'
										? questionBlock
										: answerBlock,
									{
										message: message.message,
									}
								);
								if ( message.author === 'tutor' ) {
									return (
										<FrontendAiAnswer
											key={ targetBlock.clientId }
											{ ...targetBlock }
										></FrontendAiAnswer>
									);
								}
								return (
									<FrontendStudentResponse
										key={ targetBlock.clientId }
										{ ...targetBlock }
									>
										{ targetBlock.innerBlocks.map(
											( innerBlock ) => {
												return (
													<BlockFrontend
														key={
															innerBlock.clientId
														}
														block={ innerBlock }
													/>
												);
											}
										) }
									</FrontendStudentResponse>
								);
							} ) }
						<BlockFrontend
							key={ questionBlock.clientId }
							block={ cloneBlock( questionBlock, {
								...questionBlock.attributes,
								message: undefined,
							} ) }
						/>
						<BlockFrontend
							key={ answerBlock.clientId }
							block={ cloneBlock( answerBlock, {
								...answerBlock.attributes,
								message: undefined,
							} ) }
						/>
					</div>
				</TutorAIContext.Provider>
			) : (
				<></>
			) }
		</section>
	);
}
