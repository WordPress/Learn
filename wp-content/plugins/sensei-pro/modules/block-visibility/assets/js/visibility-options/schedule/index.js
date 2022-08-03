/**
 * WordPress dependencies
 */
import { SelectControl } from '@wordpress/components';
import { Fragment, useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AfterCourseStart } from './after-course-start';
import { Timeframe } from './timeframe';

const scheduleTypes = {
	timeframe: {
		label: __( 'Selected period of time', 'sensei-pro' ),
		value: 'timeframe',
		component: Timeframe,
	},
	afterCourseStart: {
		label: __( 'Days after user enrolled to course', 'sensei-pro' ),
		value: 'afterCourseStart',
		component: AfterCourseStart,
	},
};

/**
 * Renders the Schedule component.
 *
 * @param {Object} props The WP block props.
 */
export const Schedule = ( props ) => {
	const { onChange, attributes } = props;

	// Determine the initial schedule type based on the
	// schedule settings. If there is a `daysAfterCourseStart`
	// property then it means the schedule type is "afterCourseStart".
	const { daysAfterCourseStart } =
		attributes.senseiVisibility?.SCHEDULE || {};
	const initialScheduleType = daysAfterCourseStart
		? scheduleTypes.afterCourseStart.value
		: scheduleTypes.timeframe.value;

	const [ scheduleType, setScheduleType ] = useState( initialScheduleType );
	const handleScheduleTypeChange = useCallback(
		( value ) => {
			setScheduleType( value );
			// Clear the schedule settings when the schedule
			// type is changed. Because only either one of the
			// schedule types apply. This means when one type is set
			// the values of the other type needs to be removed.
			onChange( { SCHEDULE: undefined } );
		},
		[ onChange ]
	);

	const ScheduleTypeComponent =
		scheduleTypes[ scheduleType ]?.component || Fragment;

	return (
		<div className="sensei-block-visibility__option sensei-block-visibility__schedule">
			<p className="sensei-block-visibility__option-title">
				{ __( 'Schedule Visibility', 'sensei-pro' ) }
			</p>
			<SelectControl
				options={ Object.values( scheduleTypes ) }
				onChange={ handleScheduleTypeChange }
				value={ scheduleType }
			/>
			<ScheduleTypeComponent { ...props } />
		</div>
	);
};
