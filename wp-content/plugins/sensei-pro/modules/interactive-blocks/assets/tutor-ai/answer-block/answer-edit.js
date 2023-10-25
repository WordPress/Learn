/**
 * Internal dependencies
 */
import { ReactComponent as TutorIcon } from '../../icons/tutor-ai.svg';

/**
 * External dependencies
 */
import SingleLineInput from 'sensei/assets/shared/blocks/single-line-input';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import classnames from 'classnames';
import { useBlockProps } from '@wordpress/block-editor';
import { TutorAIContext } from '../tutor-ai-edit';
import { useContext } from '@wordpress/element';

const Edit = () => {
	const blockProps = useBlockProps();

	const { setAttributes, correctAnswer } = useContext( TutorAIContext );
	return (
		<div
			{ ...blockProps }
			className={ classnames(
				blockProps.className,
				'sensei-lms-interactive-block-tutor-ai__answer'
			) }
		>
			<TutorIcon
				fill={ blockProps.style?.color }
				stroke={ blockProps.style?.color }
			/>
			<SingleLineInput
				placeholder={ __(
					'Add the correct answer your students should reach with the help of AI',
					'sensei-pro'
				) }
				value={ correctAnswer }
				onChange={ ( value ) =>
					setAttributes( { correctAnswer: value } )
				}
				maxLength={ 100 }
			/>
		</div>
	);
};

export default Edit;
