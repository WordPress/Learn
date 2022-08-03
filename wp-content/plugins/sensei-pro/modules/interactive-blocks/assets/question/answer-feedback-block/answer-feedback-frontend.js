/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Question component.
 *
 * @param {Object} props          Component props.
 * @param {string} props.correct  Whether if it's correct or not.
 * @param {Array}  props.children
 */
const AnswerFeedback = ( { correct, children } ) => {
	const type = correct ? 'correct' : 'incorrect';
	const title = correct
		? __( 'Correct', 'sensei-pro' )
		: __( 'Incorrect', 'sensei-pro' );
	return (
		<div
			className={ classnames(
				'sensei-lms-question__answer-feedback',
				`sensei-lms-question__answer-feedback--${ type }`
			) }
		>
			<div className="sensei-lms-question__answer-feedback__header">
				<span
					className={ 'sensei-lms-question__answer-feedback__icon' }
				/>
				<span>{ title }</span>
			</div>
			<div className="sensei-lms-question__answer-feedback__content">
				{ children }
			</div>
		</div>
	);
};

export default AnswerFeedback;
