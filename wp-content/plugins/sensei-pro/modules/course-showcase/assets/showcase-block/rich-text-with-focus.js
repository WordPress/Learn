/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';

/**
 * A component to extends RichText, adding a focus class.
 *
 * @param {Object} props                Component props.
 * @param {string} props.focusClassName Focus class name.
 */
const RichTextWithFocus = ( { focusClassName, ...props } ) => {
	const [ isFocused, setIsFocused ] = useState( false );

	const focusHandler = () => {
		setIsFocused( true );
	};

	const blurHandler = () => {
		setIsFocused( false );
	};

	return (
		<RichText
			{ ...props }
			className={ classnames( props.className, {
				[ focusClassName ]: isFocused,
			} ) }
			onFocus={ focusHandler }
			unstableOnFocus={ focusHandler } // See https://github.com/WordPress/gutenberg/pull/47685
			onBlur={ blurHandler }
			allowedFormats={ [] }
		/>
	);
};

export default RichTextWithFocus;
