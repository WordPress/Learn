/**
 * External dependencies
 */
import { ReactComponent as SenseiIcon } from 'sensei/assets/icons/sensei.svg';

/**
 * WordPress dependencies
 */
import { registerBlockType, updateCategory } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './shared/supports-block-id';
import './shared/supports-colors';
import './shared/block-frontend/supports-frontend';
import './shared/supports-required';
import questionBlock from './question/question-block';
import questionDescriptionBlock from './question/question-description-block';
import questionAnswersBlock from './question/question-answers-block';
import {
	answerFeedbackCorrectBlock,
	answerFeedbackIncorrectBlock,
} from './question/answer-feedback-block';
import './flashcard-block';
import './hotspots-block';
import './tasklist-block';

updateCategory( 'sensei-lms', {
	icon: <SenseiIcon width="20" height="20" />,
} );

registerBlockType( questionBlock.name, questionBlock );
registerBlockType( questionDescriptionBlock.name, questionDescriptionBlock );
registerBlockType( questionAnswersBlock.name, questionAnswersBlock );
registerBlockType(
	answerFeedbackCorrectBlock.name,
	answerFeedbackCorrectBlock
);
registerBlockType(
	answerFeedbackIncorrectBlock.name,
	answerFeedbackIncorrectBlock
);
