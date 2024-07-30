/* global wporgCourseOutlineL10n */

import { Icon, drafts, lockOutline } from '@wordpress/icons';
import { renderToString } from '@wordpress/element';

document.addEventListener( 'DOMContentLoaded', () => {
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
