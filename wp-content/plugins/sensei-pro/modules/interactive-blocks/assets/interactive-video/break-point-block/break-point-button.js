/**
 * External dependencies
 */
import classNames from 'classnames';
import { colord, extend } from 'colord';
import a11yPlugin from 'colord/plugins/a11y';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ReactComponent as LockIcon } from '../../icons/lock.svg';

// We need to extend colord with the a11yPlugin, so we can check if a text is
// readable.
extend( [ a11yPlugin ] );

/**
 * Break Point button.
 *
 * @param {Object}  props            Component props sent directly to the button.
 * @param {string}  props.className  Component classname.
 * @param {boolean} props.hasContent Whether the component has content or not.
 * @param {boolean} props.isRequired Whether the component is bigger and has the locked icon or not.
 * @param {boolean} props.isBlocked  Whether the component is blocked or not.
 */
const BreakPointButton = ( {
	className,
	hasContent,
	isRequired,
	isBlocked,
	...props
} ) => {
	const [ whiteIcon, setWhiteIcon ] = useState( false );

	const ref = useRef();

	useLayoutEffect( () => {
		if ( ! ref.current ) {
			return;
		}
		const iconColor = colord( '#000000' );
		const { backgroundColor } = window.getComputedStyle( ref.current );
		setWhiteIcon(
			isRequired &&
				! iconColor.isReadable( backgroundColor, { size: 'large' } )
		);
	}, [ isRequired, isBlocked ] );

	const message = isBlocked
		? __( 'Complete first required Break Point', 'sensei-pro' )
		: __( 'Complete to continue', 'sensei-pro' );

	return (
		// We are using an anchor <a> here so that we can retrieve the theme main color by using `currentColor` css property.
		<a
			className={ classNames(
				'wp-block-sensei-pro-break-point__button',
				className,
				{
					'wp-block-sensei-pro-break-point__button--is-empty': ! hasContent,
					'wp-block-sensei-pro-break-point__button--is-required': isRequired,
					'wp-block-sensei-pro-break-point__button--is-blocked': isBlocked,
					'wp-block-sensei-pro-break-point__button--white-icon': whiteIcon,
				}
			) }
			title={ isRequired ? message : null }
			role="button"
			ref={ ref }
			{ ...props }
		>
			{ isRequired ? (
				<LockIcon aria-label={ __( 'Required break point icon' ) } />
			) : null }
			<span className="screen-reader-text">
				{ isRequired
					? __( 'Required break point content', 'sensei-pro' )
					: __( 'Break point content', 'sensei-pro' ) }
			</span>
		</a>
	);
};

export default BreakPointButton;
