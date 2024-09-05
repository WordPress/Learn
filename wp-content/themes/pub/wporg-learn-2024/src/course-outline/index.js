/* global wporgCourseOutlineL10n */

import { Icon, drafts, lockOutline } from '@wordpress/icons';
import { renderToString } from '@wordpress/element';

document.addEventListener( 'DOMContentLoaded', () => {
	/**
	 * Allow the entire header to toggle, giving users a larger area to interact with.
	 */
	document
		.querySelectorAll( 'section.wp-block-sensei-lms-course-outline-module-bordered > header' )
		.forEach( ( header ) => {
			const button = header.querySelector( 'button' );
			header.addEventListener( 'click', () => {
				button.click();
			} );

			button.addEventListener( 'click', ( event ) => {
				event.stopPropagation();
			} );

			// To enable the entire header to be clickable for toggling without conflicts, remove the link.
			// In fact, this link duplicates the first lesson link in the course outline.
			// See https://github.com/WordPress/Learn/pull/2776#issuecomment-2258308422
			const link = header.querySelector( 'h2 > a' );
			if ( link ) {
				const heading = link.parentElement;
				heading.innerHTML = link.innerHTML;
			}
			// Also remove the link within the span with class "screen-reader-text" if it exists.
			const span = header.querySelector( 'span.screen-reader-text' );
			if ( span ) {
				const spanLink = span.querySelector( 'a' );
				if ( spanLink ) {
					const spanContent = spanLink.innerHTML;
					span.innerHTML = spanContent;
				}
			}
		} );

	/**
	 * Find all in progress lessons, and replace the status icon with the Gutenberg-style `drafts` icon.
	 */
	document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson.is-in-progress' ).forEach( ( link ) => {
		const statusIcon = link.querySelector( '.wp-block-sensei-lms-course-outline-lesson__status' );
		if ( statusIcon ) {
			const iconString = renderToString(
				<Icon
					icon={ drafts }
					style={ { transform: 'scale(1.5)' } }
					aria-label={ wporgCourseOutlineL10n.inProgress }
					role="img"
				/>
			);

			// Remove the `aria-hidden` attribute from the icon, as it has a readable label.
			statusIcon.outerHTML = iconString.replace( ' aria-hidden="true"', '' );
		}
	} );

	/**
	 * Find all locked lessons, and inject a `lock` icon after the title.
	 */
	document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson.is-locked' ).forEach( ( link ) => {
		const span = link.querySelector( 'span' );
		if ( span ) {
			span.insertAdjacentHTML(
				'afterend',
				renderToString(
					<>
						<Icon icon={ lockOutline } />
						<span className="screen-reader-text">{ wporgCourseOutlineL10n.locked }</span>
					</>
				)
			);
		}
	} );
} );
