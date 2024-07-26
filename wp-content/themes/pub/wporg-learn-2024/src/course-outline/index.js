/* global wporgCourseOutlineData, wporgCourseOutlineL10n */

import { Icon, drafts, lockOutline } from '@wordpress/icons';
import { renderToString } from '@wordpress/element';

document.addEventListener( 'DOMContentLoaded', () => {
	if ( ! wporgCourseOutlineData ) {
		return;
	}

	wporgCourseOutlineData[ 'in-progress' ]?.forEach( ( title ) => {
		const lessonLinks = document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson' );
		lessonLinks.forEach( ( link ) => {
			const span = link.querySelector( 'span' );
			if ( span && span.textContent.trim() === title ) {
				const statusIcon = link.querySelector( '.wp-block-sensei-lms-course-outline-lesson__status' );
				if ( statusIcon ) {
					statusIcon.outerHTML = renderToString(
						<>
							<Icon icon={ drafts } style={ { transform: 'scale(1.5)' } } />
							<span className="screen-reader-text">{ wporgCourseOutlineL10n.inProgress }</span>
						</>
					);
				}
			}
		} );
	} );
	wporgCourseOutlineData.locked?.forEach( ( title ) => {
		const lessonLinks = document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson' );
		lessonLinks.forEach( ( link ) => {
			const span = link.querySelector( 'span' );
			if ( span && span.textContent.trim() === title ) {
				span.insertAdjacentHTML( 'afterend', renderToString( <Icon icon={ lockOutline } /> ) );
			}
		} );
	} );
} );
