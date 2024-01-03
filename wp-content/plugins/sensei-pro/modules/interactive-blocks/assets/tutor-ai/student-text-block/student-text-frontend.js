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
import classnames from 'classnames';

function FrontendStudentResponse( props, ref ) {
	const { blockProps } = props;
	const [ studentText, setStudentText ] = useState( '' );
	const avatarBlock = props.children?.length > 0 ? props.children[ 0 ] : null;
	const { gotCorrectAnswer, insertMessage, inputRef, isAiBusy } = useContext(
		TutorAIContext
	);

	const customBlockProps = {
		...blockProps,
		ref,
		className: classnames( blockProps.className, 'block-frontend' ),
	};

	if ( ! props.attributes?.message && ( isAiBusy || gotCorrectAnswer ) ) {
		return <></>;
	}

	const handleSubmit = ( e ) => {
		e.preventDefault();

		if ( studentText ) {
			insertMessage( {
				message: studentText,
				author: 'student',
			} );
		}
	};

	const handleChange = ( e ) => {
		setStudentText( e.target.value );
		e.target.style.height = 'auto'; // It sets the height as "auto" first to reset the height for when removing lines.
		e.target.style.height = e.target.scrollHeight + 'px';
	};

	const handleKeyDown = ( e ) => {
		if ( e.key === 'Enter' ) {
			handleSubmit( e );
		}
	};

	return (
		<div { ...customBlockProps }>
			{ avatarBlock }
			{ props.attributes?.message ? (
				<div>{ props.attributes?.message }</div>
			) : (
				<form
					className="sensei-pro-tutor-ai__form"
					onSubmit={ handleSubmit }
				>
					<textarea
						className="sensei-pro-tutor-ai__input"
						ref={ inputRef }
						placeholder={ __(
							'Type your answer here…',
							'sensei-pro'
						) }
						onChange={ handleChange }
						onKeyDown={ handleKeyDown }
						maxLength={ 100 }
						rows={ 1 }
						value={ studentText }
					/>
					<button className="sensei-pro-tutor-ai__answer-submit">
						<ArrowIcon />
					</button>
				</form>
			) }
		</div>
	);
}

export default forwardRef( FrontendStudentResponse );
