/**
 * WordPress dependencies
 */
import { Button, Fill, Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ReactComponent as AiIcon } from '../icons/ai-icon.svg';
import { addFilter } from '@wordpress/hooks';
import { compose } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getLessonContent } from './block-text-scrapper';
import { useGPTQuestionGenerator } from './hooks/use-chat-gpt-qestion-generator';

const withQuestionGeneratorButton = ( BlockEdit ) => ( props ) => {
	const {
		isBusy,
		generateQuizQuestions,
		limitErrorMessage,
		disabledByTimeout,
		hasQuestions,
	} = useGPTQuestionGenerator();

	const [ showErrorNotice, setShowErrorNotice ] = useState( false );
	const [ firstTimeUser, setFirstTimeUser ] = useState( true );
	const [ showFirstTimeUserNotice, setShowFirstTimeUserNotice ] = useState(
		false
	);

	const { user } = useSelect( ( select ) => ( {
		user: select( 'core' ).getCurrentUser(),
	} ) );

	useEffect( () => {
		if ( user && user.meta && user.meta.sensei_pro_question_ai_used ) {
			setFirstTimeUser( false );
		}
	}, [ user ] );

	const doProcessing = () => {
		setShowFirstTimeUserNotice( firstTimeUser );
		if ( firstTimeUser ) {
			setFirstTimeUser( false );
		}
		const lessonContent = getLessonContent();
		generateQuizQuestions( lessonContent, 3 );
		setShowErrorNotice( true );
	};

	return (
		<>
			<Fill name="SenseiQuizHeader">
				<Button
					variant="secondary"
					onClick={ doProcessing }
					className="sensei-pro-ai-generate-questions-button"
					disabled={ isBusy || hasQuestions || disabledByTimeout }
				>
					<div className="button-text-content">
						<AiIcon />
						{ __(
							'Generate quiz questions with AI',
							'sensei-pro'
						) }
					</div>
				</Button>
			</Fill>
			{ isBusy && (
				<Fill name="SenseiQuizBlockTop">
					<div className="sensei-pro-quiz-question-generate-loader">
						<Spinner />
					</div>
				</Fill>
			) }
			{ disabledByTimeout && showErrorNotice && (
				<Fill name="SenseiQuizBlockTop">
					<Notice
						className="sensei-pro-quiz-question-generate-notice"
						dismissible={ false }
						onDismiss={ () => {
							setShowErrorNotice( false );
						} }
						status="warning"
					>
						{ limitErrorMessage }
					</Notice>
				</Fill>
			) }
			{ showFirstTimeUserNotice && (
				<Fill name="SenseiQuizBlockTop">
					<Notice
						className="sensei-pro-quiz-question-generate-notice"
						dismissible={ false }
						onDismiss={ () => {
							setShowFirstTimeUserNotice( false );
						} }
						status="info"
					>
						{ __(
							'Using AI can feel like magic. But make sure to double check the text and make changes as you see fit.',
							'sensei-pro'
						) }
					</Notice>
				</Fill>
			) }
			<BlockEdit { ...props } />
		</>
	);
};

export const addQuestionGeneratorToQuizBlock = ( settings ) => {
	if ( 'sensei-lms/quiz' !== settings.name ) {
		return settings;
	}

	return {
		...settings,
		edit: compose( withQuestionGeneratorButton )( settings.edit ),
	};
};

addFilter(
	'blocks.registerBlockType',
	'sensei-lms/with-chat-gpt-question-generator',
	addQuestionGeneratorToQuizBlock
);
