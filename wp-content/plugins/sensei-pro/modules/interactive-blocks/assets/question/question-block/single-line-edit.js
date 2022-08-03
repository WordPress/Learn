/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import SingleLineAnswer from 'sensei/assets/blocks/quiz/answer-blocks/single-line';

/**
 * Single-line question edit component.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {string}   props.attributes.answers Valid answers.
 * @param {Function} props.setAttributes
 */
export const SingleLineQuestionEdit = ( {
	attributes: { answers = [] },
	setAttributes,
} ) => {
	return (
		<div className="sensei-lms-interactive-block-question__single-line">
			<SingleLineAnswer>
				<input
					type="text"
					className="sensei-lms-interactive-block-question__single-line-input"
					aria-label={ __( 'The correct answer', 'sensei-pro' ) }
					placeholder={ __( 'Add answer', 'sensei-pro' ) }
					value={ answers[ 0 ] ?? '' }
					onChange={ ( event ) =>
						setAttributes( { answers: [ event.target.value ] } )
					}
				/>
			</SingleLineAnswer>
		</div>
	);
};
