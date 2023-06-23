/**
 * WordPress dependencies
 */
import { forwardRef, useCallback, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ReactComponent as ToggleIcon } from '../../../assets/icons/accordion-arrow-down.svg';
import { SectionContext } from './section';
/**
 * External dependencies
 */
import { isEmpty, noop } from 'lodash';
import classNames from 'classnames';

const Summary = ( props, ref ) => {
	const { children, onEnter = noop, ...otherProps } = props;
	const { fontSize, style } = props.attributes;
	const { toggleCurrentSection, isEditor } = useContext( SectionContext );

	// It prevents open/close the accordion when the user is typing inside the accordion
	const cancelOnSpacingPress = ( e ) => {
		if ( e.key === ' ' ) e.preventDefault();
	};

	const handleClick = useCallback(
		( e ) => {
			e.preventDefault();
			toggleCurrentSection();
		},
		[ toggleCurrentSection ]
	);

	const hasCustomFontSize =
		! isEmpty( fontSize ) || ! isEmpty( style, 'typography.fontSize' );

	const cancelEnterKey = useCallback(
		( e ) => {
			if ( e.key !== 'Enter' ) return;
			e.preventDefault();
			onEnter();
		},
		[ onEnter ]
	);

	const cancelClick = ( e ) => e.preventDefault();

	const classes = classNames( otherProps.className, {
		'has-custom-font-size': hasCustomFontSize,
	} );

	return (
		<summary
			ref={ ref }
			{ ...otherProps }
			onKeyUp={ cancelOnSpacingPress }
			onKeyDownCapture={ cancelEnterKey }
			onClick={ isEditor ? cancelClick : handleClick }
			className={ classes }
		>
			{ children }

			<button
				label={ __( 'Open/close the section', 'sensei-pro' ) }
				className={ 'wp-block-sensei-lms-accordion-summary__toggle' }
				onClick={ isEditor ? handleClick : null }
				tabIndex={ isEditor ? null : -1 }
			>
				<ToggleIcon />
			</button>
		</summary>
	);
};

export default forwardRef( Summary );
