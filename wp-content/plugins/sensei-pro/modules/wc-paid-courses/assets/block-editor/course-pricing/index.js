/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { Fill } from '@wordpress/components';

/**
 * Internal dependencies
 */
import CoursePricingSidebar from './course-pricing-sidebar';

registerPlugin( 'sensei-wc-paid-courses-pricing-sidebar-plugin', {
	render: () => (
		<Fill name="SenseiCourseSidebar">
			<CoursePricingSidebar />
		</Fill>
	),
	icon: null,
} );
