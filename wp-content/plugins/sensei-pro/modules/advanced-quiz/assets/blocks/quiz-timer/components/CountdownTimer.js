/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Wordpress dependencies
 */
import { useEffect, useState, forwardRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const CountdownTimer = forwardRef(
	(
		{
			seconds,
			triggerOnRender,
			deadline,
			isCourseTheme,
			isSmall = true,
			isSticky = false,
			isHidden = false,
			style = {},
			showTextOnlyTimer = false,
		},
		ref
	) => {
		let size = 106;
		let strokeWidth = 4;

		if ( isSmall ) {
			size = 81;
		}

		const milliseconds = seconds * 1000;
		const radius = size / 2;
		const circumference = ( size - strokeWidth ) * Math.PI;
		const [ countdown, setCountdown ] = useState( deadline - Date.now() );
		const [ isPlaying, setIsPlaying ] = useState( false );

		useEffect( () => {
			setCountdown( deadline - Date.now() );
		}, [ deadline ] );

		useEffect( () => {
			if ( ! triggerOnRender || countdown <= 0 ) {
				return;
			}
			setIsPlaying( true );
			const timer = setTimeout( () => {
				setCountdown( deadline - Date.now() );
				if ( countdown <= 0 ) {
					clearTimeout( timer );
					setCountdown( milliseconds );
				}
			}, 10 );
		}, [ countdown ] );

		const strokeDashoffset = () =>
			circumference - ( countdown / milliseconds ) * circumference;

		const countdownSizeStyles = {
			height: size,
			width: size,
		};

		const textStyles = {
			color: 'black',
			fontSize: size * 0.2,
			fontFamily: 'sans-serif',
		};

		const getDisplayTime = ( duration ) => {
			if ( duration < 1 ) {
				duration = 0;
			}
			let s = Math.floor( ( duration / 1000 ) % 60 );
			let m = Math.floor( ( duration / ( 1000 * 60 ) ) % 60 );
			let h = Math.floor( ( duration / ( 1000 * 60 * 60 ) ) % 24 );

			h = h < 10 ? '0' + h : h;
			m = m < 10 ? '0' + m : m;
			s = s < 10 ? '0' + s : s;

			return ( h === '00' ? '' : h + ':' ) + m + ':' + s;
		};

		if ( showTextOnlyTimer ) {
			return (
				<div
					className={ classnames(
						'sensei-lms-quiz-timer__countdown-text--sticky',
						'sensei-course-theme__main-content'
					) }
				>
					<div className="sensei-lms-quiz-timer__countdown-text--inner-container">
						<span className="sensei-lms-quiz-timer__countdown-text--label">
							{ __( 'Time left', 'sensei-pro' ) }
						</span>
						<span className="sensei-lms-quiz-timer__countdown-text--time">
							{ getDisplayTime( countdown ) }
						</span>
					</div>
				</div>
			);
		}

		return (
			<div
				ref={ ref }
				className={ classnames( {
					'sensei-lms-quiz-timer__countdown-circular': true,
					'sensei-lms-quiz-timer__countdown-circular--sticky': showTextOnlyTimer
						? false
						: isSticky,
					'sensei-lms-quiz-timer__countdown-circular--hidden': isHidden,
					'sensei-lms-quiz-timer-course-theme': isCourseTheme,
				} ) }
				style={ style }
			>
				<div
					className="sensei-lms-quiz-timer__countdown-circular__circle-container"
					style={ countdownSizeStyles }
				>
					<div style={ textStyles }>
						{ getDisplayTime( countdown ) }
					</div>
					<svg>
						<circle
							style={ {
								r: radius - strokeWidth / 2,
								cx: radius,
								cy: radius,
								strokeWidth,
								fill: 'transparent',
								strokeOpacity: 0.2,
							} }
						/>
						<circle
							style={ {
								strokeDasharray: circumference,
								strokeDashoffset:
									isPlaying ||
									( ! isPlaying && countdown > 0 )
										? strokeDashoffset()
										: circumference,
								r: radius - strokeWidth / 2,
								cx: radius,
								cy: radius,
								strokeWidth,
								fill: 'transparent',
							} }
						/>
					</svg>
				</div>
			</div>
		);
	}
);
