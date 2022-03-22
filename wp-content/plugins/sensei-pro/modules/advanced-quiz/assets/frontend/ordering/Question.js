/**
 * Internal dependencies.
 */
import Answer from './Answer';
import { SortableList } from './SortableList';

/**
 * @typedef AnswerType
 *
 * @property {string} id    Unique id of the answer.
 * @property {string} label Content of the answer.
 */

/**
 * Question
 *
 * @param {Object}            props
 * @param {string}            props.id      The WP post id of the question.
 * @param {Array<AnswerType>} props.answers The answers of the question.
 *
 */
export default function Question( { id, answers = [] } ) {
	const Answers = answers.map( ( answer ) => ( {
		Component: Answer,
		...answer,
		questionId: id,
	} ) );

	return (
		<ol className={ `sensei-ordering-answers` }>
			<SortableList items={ Answers } />
		</ol>
	);
}
