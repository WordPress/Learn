/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { CountdownTimer } from './CountdownTimer';

const WP_ADMIN_BAR_ELEMENT_SELECTOR =
	'#wpadminbar,.sensei-course-theme__header';
const TIMER_FIXED_POSITION_OFFSET = 20;
const SMALL_SCREEN_BREAKPOINT = 782;

export const Timer = ( {
	time,
	isPreviewMode = false,
	isCourseTheme,
	...props
} ) => {
	const [ isSticky, setIsSticky ] = useState( false );
	const [ adminBarBottom, setAdminBarBottom ] = useState( 0 );
	const [ isSmallScreen, setIsSmallScreen ] = useState( true );
	const timerRef = useRef( null );

	const handleWindowResize = useCallback( () => {
		const html = document.querySelector( 'html' );
		setIsSmallScreen( html && html.clientWidth <= SMALL_SCREEN_BREAKPOINT );
		const adminBar = document?.querySelector(
			WP_ADMIN_BAR_ELEMENT_SELECTOR
		);
		if ( ! adminBar ) {
			return;
		}

		const position = getComputedStyle( adminBar ).getPropertyValue(
			'position'
		);
		if ( position !== 'fixed' ) {
			setAdminBarBottom( 0 );
			return;
		}

		const { bottom } = adminBar?.getBoundingClientRect() || { bottom: 0 };
		setAdminBarBottom( bottom );
	}, [ setAdminBarBottom ] );

	const handleScroll = useCallback( () => {
		if ( ! timerRef.current ) {
			return;
		}

		const { top, bottom } = timerRef.current.getBoundingClientRect() || {};
		const height = bottom - top;
		setIsSticky( bottom - height / 2 <= adminBarBottom );
	}, [ setIsSticky, adminBarBottom ] );

	useEffect( () => {
		document.addEventListener( 'scroll', handleScroll );
		window.addEventListener( 'resize', handleWindowResize );

		// update at least once.
		handleWindowResize();

		return () => {
			document.removeEventListener( 'scroll', handleScroll );
			window.removeEventListener( 'resize', handleWindowResize );
		};
	}, [ handleScroll, handleWindowResize ] );

	const showTextOnlyCountdown =
		( isSmallScreen || isSticky ) && isCourseTheme;

	useEffect( () => {
		const courseThemeMainContent = document.querySelector(
			'main.sensei-course-theme__main-content'
		);

		if ( showTextOnlyCountdown ) {
			courseThemeMainContent.classList.add(
				'sensei-course-theme__main-content--sticky-quiz-timer'
			);
		} else {
			courseThemeMainContent &&
				courseThemeMainContent.classList.remove(
					'sensei-course-theme__main-content--sticky-quiz-timer'
				);
		}
	}, [ showTextOnlyCountdown ] );

	return (
		<>
			{ /* This is the main timer that shows up at the beginning of the quiz content. */ }
			<CountdownTimer
				seconds={ time }
				triggerOnRender={ isPreviewMode }
				isHidden={ isSticky || showTextOnlyCountdown }
				isSmall={ isSmallScreen }
				ref={ timerRef }
				isCourseTheme={ isCourseTheme }
				{ ...props }
			/>

			{ /* This is the sticky timer that shows up when the main timer is outside the viewport.  */ }
			{ ! isCourseTheme && (
				<CountdownTimer
					seconds={ time }
					triggerOnRender={ isPreviewMode }
					isSticky
					isHidden={ ! isSticky }
					isSmall={ isSmallScreen }
					style={ {
						top: `${
							adminBarBottom + TIMER_FIXED_POSITION_OFFSET
						}px`,
						right: `${ TIMER_FIXED_POSITION_OFFSET }px`,
					} }
					{ ...props }
				/>
			) }
			{
				// This is the sticky timer that shows up in Learning Mode when the main timer is outside the viewport.
				isCourseTheme && showTextOnlyCountdown && (
					<CountdownTimer
						seconds={ time }
						triggerOnRender={ isPreviewMode }
						isSmall={ isSmallScreen }
						isCourseTheme={ isCourseTheme }
						showTextOnlyTimer={ true }
						{ ...props }
					/>
				)
			}
		</>
	);
};
