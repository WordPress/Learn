/* global wporgCourseOutlineData */

import { Icon, drafts, lockOutline } from '@wordpress/icons';
import { renderToString } from 'react-dom/server';

document.addEventListener( 'DOMContentLoaded', function () {
	const lessonData = wporgCourseOutlineData;

	lessonData[ 'in-progress' ]?.forEach( function ( lessonTitle ) {
		const title = lessonTitle;
		const icon = renderToString( <Icon icon={ drafts } transform={ 'scale(1.5)' } /> );

		const lessonLinks = document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson' );

		lessonLinks.forEach( function ( link ) {
			const span = link.querySelector( 'span' );

			if ( span && span.textContent.trim() === title ) {
				const statusIcon = link.querySelector( '.wp-block-sensei-lms-course-outline-lesson__status' );
				if ( statusIcon ) {
					statusIcon.outerHTML = icon;
				}
			}
		} );
	} );

	lessonData.locked?.forEach( function ( lessonTitle ) {
		const title = lessonTitle;
		const icon = renderToString( <Icon icon={ lockOutline } /> );

		const lessonLinks = document.querySelectorAll( '.wp-block-sensei-lms-course-outline-lesson' );

		lessonLinks.forEach( function ( link ) {
			const span = link.querySelector( 'span' );

			if ( span && span.textContent.trim() === title ) {
				span.insertAdjacentHTML( 'afterend', icon );
			}
		} );
	} );
} );
