/**
 * External dependencies
 */
import questionAnswersBlock from 'sensei/assets/blocks/quiz/question-answers-block';
import { ReactComponent as icon } from 'sensei/assets/icons/question.svg';

/**
 * Internal dependencies
 */
import questionBlock from '../question-block';

// New block reusing the edit component from Sensei Core's QuestionDescriptionBlock.
export default {
	...questionAnswersBlock,
	name: 'sensei-pro/question-answers',
	supports: {
		sensei: {
			frontend: true,
		},
	},
	icon,
	parent: [ questionBlock.name ],
	save: ( { children, blockProps } ) => {
		return <div { ...blockProps }>{ children }</div>;
	},
	attributes: {
		...questionAnswersBlock.attributes,
		slot: { type: 'string' },
	},
};
