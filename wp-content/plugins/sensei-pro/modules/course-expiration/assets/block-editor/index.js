/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import CourseExpirationSidebar from './course-expiration-sidebar';

registerPlugin( 'sensei-pro-course-expiration-plugin', {
	render: CourseExpirationSidebar,
	icon: null,
} );
