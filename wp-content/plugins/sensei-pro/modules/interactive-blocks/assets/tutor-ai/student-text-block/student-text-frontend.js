/**
 * WordPress dependencies
 */
import { forwardRef, useContext, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { ReactComponent as ArrowIcon } from '../../icons/arrow-down.svg';
import { TutorAIContext } from '../frontend';
/**
 * External dependencies
 */
import SingleLineInput from 'sensei/assets/shared/blocks/single-line-input';
import classnames from 'classnames';

function FrontendStudentResponse( props, ref ) {
	const { blockProps } = props;
	const [ studentText, setStudentText ] = useState( '' );
	const avatarBlock = props.children?.length > 0 ? props.children[ 0 ] : null;
	const { gotCorrectAnswer, insertMessage, inputRef, isAiBusy } = useContext(
		TutorAIContext
	);

	const blockPropsWithAdditionalClass = {
		...blockProps,
		className: classnames( blockProps.className, 'block-frontend' ),
	};

	if ( ! props.attributes?.message && ( isAiBusy || gotCorrectAnswer ) ) {
		return <></>;
	}

	const handleClick = () => {
		if ( studentText ) {
			insertMessage( {
				message: studentText,
				author: 'student',
			} );
		}
	};

	return (
		<div { ...blockPropsWithAdditionalClass } ref={ ref }>
			{ avatarBlock }
			{ props.attributes?.message ? (
				<div>{ props.attributes?.message }</div>
			) : (
				<>
					<SingleLineInput
						ref={ inputRef }
						placeholder={ __(
							'Type your answer here…',
							'sensei-pro'
						) }
						onChange={ ( value ) => {
							setStudentText( value );
						} }
						onEnter={ handleClick }
						maxLength={ 100 }
					></SingleLineInput>
					<button
						className="sensei-pro-tutor-ai__answer-submit"
						onClick={ handleClick }
					>
						<ArrowIcon />
					</button>
				</>
			) }
		</div>
	);
}

export default forwardRef( FrontendStudentResponse );
