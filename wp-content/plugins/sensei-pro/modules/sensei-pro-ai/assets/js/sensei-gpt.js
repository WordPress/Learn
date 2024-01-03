/**
 * WordPress dependencies
 */
import { Button, Fill, Spinner } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { ReactComponent as AiIcon } from '../icons/ai-icon.svg';
import { getLessonContent } from './block-text-scrapper';
import { useGPTQuestionGenerator } from './hooks/use-chat-gpt-qestion-generator';

const notices = {
	lessonNoContent: __(
		"Oops! Looks like your lesson doesn't have any content. Quiz questions are generated based on the current content of the lesson.",
		'sensei-pro'
	),
};

const withQuestionGeneratorButton = ( BlockEdit ) => ( props ) => {
	const {
		isBusy,
		generateQuizQuestions,
		limitErrorMessage,
		disabledByTimeout,
		hasQuestions,
	} = useGPTQuestionGenerator();

	const [ errorMessage, setErrorMessage ] = useState( null );
	const [ showErrorNotice, setShowErrorNotice ] = useState( false );

	const doProcessing = () => {
		const lessonContent = getLessonContent();

		if ( ! lessonContent ) {
			setErrorMessage( notices.lessonNoContent );
			setShowErrorNotice( true );
			return;
		} else if ( errorMessage === notices.lessonNoContent ) {
			setErrorMessage( null );
			setShowErrorNotice( false );
		}

		generateQuizQuestions( lessonContent, 3 );
	};

	useEffect( () => {
		setShowErrorNotice( disabledByTimeout );

		if ( disabledByTimeout ) {
			setErrorMessage( limitErrorMessage );
		}
	}, [ disabledByTimeout, limitErrorMessage ] );

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
			{ showErrorNotice && (
				<Fill name="SenseiQuizBlockTop">
					<div className="sensei-message info">{ errorMessage }</div>
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
