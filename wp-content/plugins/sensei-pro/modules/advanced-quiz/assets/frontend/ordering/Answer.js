/**
 * External dependencies.
 */
import { forwardRef } from '@wordpress/element';
import { Icon, dragHandle } from '@wordpress/icons';
import classnames from 'classnames';

/**
 * Answer
 *
 * @param {Object}   props
 * @param {string}   props.id         Unique id of the answer.
 * @param {string}   props.label      The content of the answer.
 * @param {string}   props.questionId The id of the question this answer related to.
 * @param {Function} props.isDragging Tells if the item is being dragged.
 * @param {Object}   props.style      Dynamic css styles.
 * @param {boolean}  props.correct    Whether the answer was ordered correctly.
 * @param {Object}   ref
 */
function Answer(
	{ id, label, questionId, isDragging, correct: isCorrect, ...props },
	ref
) {
	const hasResult = typeof isCorrect !== 'undefined';
	const className = classnames( {
		'sensei-ordering-answer': true,
		'sensei-ordering-answer--draggable': true,
		'sensei-ordering-answer--dragging': isDragging,
		'sensei-ordering-answer--correct': isCorrect,
		'sensei-ordering-answer--wrong': isCorrect === false,
	} );

	return (
		<li ref={ ref } id={ id } className={ className } { ...props }>
			<input
				id={ `question_${ questionId }_answer-${ id }` }
				type="hidden"
				name={ `sensei_question[${ questionId }][]` }
				value={ id }
			/>
			<label
				className="sensei-ordering-answer__label"
				htmlFor={ `question_${ questionId }_answer-${ id }` }
			>
				<div className="sensei-ordering-answer__icon">
					{ ! hasResult && <Icon icon={ dragHandle } size={ 18 } /> }
				</div>
				<div className="sensei-ordering-answer__content">{ label }</div>
				{ hasResult && (
					<span className="sensei-ordering-answer__result-icon" />
				) }
			</label>
		</li>
	);
}

export default forwardRef( Answer );
