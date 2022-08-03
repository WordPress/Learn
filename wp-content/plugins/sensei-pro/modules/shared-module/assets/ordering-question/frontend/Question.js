/**
 * Internal dependencies
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
 * Question component.
 *
 * @param {Object}            props
 * @param {string}            props.id       The WP post id of the question.
 * @param {Array<AnswerType>} props.answers  The answers of the question.
 * @param {boolean}           props.disabled Whether sorting is disabled.
 * @param {Function}          props.onChange Change callback.
 */
export default function Question( {
	id,
	answers = [],
	disabled = false,
	onChange,
} ) {
	const Answers = answers.map( ( answer ) => ( {
		Component: Answer,
		...answer,
		questionId: id,
	} ) );

	return (
		<ol className={ `sensei-ordering-answers` }>
			<SortableList
				items={ Answers }
				disabled={ disabled }
				onChange={ onChange }
			/>
		</ol>
	);
}
