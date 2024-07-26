/* global wporgCourseOutlineL10n */

import { Icon, drafts, lockOutline } from '@wordpress/icons';
import { renderToString } from '@wordpress/element';

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson.is-in-progress' ).forEach( ( link ) => {
		const statusIcon = link.querySelector( '.wp-block-sensei-lms-course-outline-lesson__status' );
		if ( statusIcon ) {
			statusIcon.outerHTML = renderToString(
				<>
					<Icon icon={ drafts } style={ { transform: 'scale(1.5)' } } />
					<span className="screen-reader-text">{ wporgCourseOutlineL10n.inProgress }</span>
				</>
			);
		}
	} );

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
