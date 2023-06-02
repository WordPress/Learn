/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import CourseExpirationSidebar from './course-expiration-sidebar';
import { Fill } from '@wordpress/components';

registerPlugin( 'sensei-pro-course-expiration-plugin', {
	render: () => (
		<Fill name="SenseiCourseSidebar">
			<CourseExpirationSidebar />
		</Fill>
	),
	icon: null,
} );
