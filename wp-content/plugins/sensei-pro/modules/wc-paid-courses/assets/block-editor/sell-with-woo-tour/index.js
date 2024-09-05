/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import getTourSteps, { beforeEach } from './steps';
import { useState } from '@wordpress/element';

/**
 * External dependencies
 */
import SenseiTourKit from 'sensei/assets/admin/tour/components/sensei-tour-kit';

const tourName = 'sensei-sell-course-with-woo-tour';

export default function SellCourseWithWooTour() {
	const [ tourSteps ] = useState( getTourSteps() );

	return (
		<SenseiTourKit
			trackId="sell_course_with_woo_onboarding_step_complete"
			tourName={ tourName }
			steps={ tourSteps }
			beforeEach={ beforeEach }
		/>
	);
}

registerPlugin( tourName, {
	render: () => <SellCourseWithWooTour />,
} );
