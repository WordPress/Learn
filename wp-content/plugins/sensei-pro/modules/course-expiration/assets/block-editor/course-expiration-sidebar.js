/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __, sprintf } from '@wordpress/i18n';
import {
	Button,
	DatePicker,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useState } from '@wordpress/element';

/**
 * External dependencies
 */
import moment from 'moment';

/**
 * Internal dependencies
 */
import {
	EXPIRATION_TYPE,
	EXPIRATION_LENGTH,
	EXPIRATION_PERIOD,
	EXPIRATION_DATE,
	NO_EXPIRATION,
	EXPIRES_AFTER,
	EXPIRES_ON,
	MONTH,
	WEEK,
	DAY,
	IMMEDIATELY,
	STARTS_ON,
	START_TYPE,
	START_DATE,
} from './constants';

const expiresCourseAccessPeriodLabel = __(
	'Set Course Expiration Date',
	'sensei-pro'
);

const startCourseAccessPeriodLabel = __(
	'Set Course Start Date',
	'sensei-pro'
);

const format = ( date ) => moment( date ).format( moment.HTML5_FMT.DATE );

/**
 * A hook that provides a value from course meta and a setter for that value.
 *
 * @param {string} metaName The name of the meta.
 *
 * @return {Array} An array containing the value and the setter.
 */
const useCourseMeta = ( metaName ) => {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'course', 'meta' );
	const value = meta[ metaName ];
	const setter = ( newValue ) => setMeta( { [ metaName ]: newValue } );

	return [ value, setter ];
};

/**
 * Course Expiration Sidebar component.
 */
const CourseExpirationSidebar = () => {
	const [ expirationType, setExpirationType ] = useCourseMeta(
		EXPIRATION_TYPE
	);

	const [ startType, setStartType ] = useCourseMeta( START_TYPE );

	const [ showExpirationDatePicker, setShowExpirationDatePicker ] = useState(
		false
	);
	const [ showStartDatePicker, setShowStartDatePicker ] = useState( false );

	const [ expirationLength, setExpirationLength ] = useCourseMeta(
		EXPIRATION_LENGTH
	);

	const [ expirationDate, setExpirationDate ] = useCourseMeta(
		EXPIRATION_DATE
	);
	const [ startDate, setStartDate ] = useCourseMeta( START_DATE );

	const [ expirationPeriod, setExpirationPeriod ] = useCourseMeta(
		EXPIRATION_PERIOD
	);

	const pickExpirationDate = ( newDate ) => {
		setExpirationDate( newDate );
		setShowExpirationDatePicker( false );
	};

	const pickStartDate = ( newDate ) => {
		setStartDate( newDate );
		setShowStartDatePicker( false );
	};

	const onExpirationLengthChange = ( value ) => {
		// Sanitize it to a number greater than or equal 1.
		const sanitizedValue = Math.max(
			1,
			parseInt( value.replace( /\D/g, '' ) || 1, 10 )
		);
		setExpirationLength( sanitizedValue );
	};

	const onExpirationLengthKeyPress = ( e ) => {
		// Avoid non-digit chars.
		if ( /\D/.test( e.key ) ) {
			e.preventDefault();
		}
	};

	const expiresAfterForm = (
		<>
			<div className="sensei-wcpc-course-expiration__expires-after">
				<TextControl
					className="sensei-wcpc-course-expiration__expiration-length"
					label={ __( 'Expiration Length', 'sensei-pro' ) }
					hideLabelFromVision
					type="number"
					step={ 1 }
					min={ 1 }
					value={ expirationLength }
					onChange={ onExpirationLengthChange }
					onKeyPress={ onExpirationLengthKeyPress }
				/>
				<SelectControl
					label={ __( 'Expiration Period', 'sensei-pro' ) }
					hideLabelFromVision
					value={ expirationPeriod }
					options={ [
						{
							label: __( 'Month(s)', 'sensei-pro' ),
							value: MONTH,
						},
						{
							label: __( 'Week(s)', 'sensei-pro' ),
							value: WEEK,
						},
						{
							label: __( 'Day(s)', 'sensei-pro' ),
							value: DAY,
						},
					] }
					onChange={ setExpirationPeriod }
				/>
			</div>

			{ DAY === expirationPeriod && 1 === expirationLength && (
				<small className="sensei-wcpc-course-expiration__help-text">
					{ __(
						'The learner access will expire at midnight on the day of enrollment.',
						'sensei-pro'
					) }
				</small>
			) }
		</>
	);

	const accessPeriodDatePicker = (
		setShowDatePicker,
		date,
		label,
		shouldDisplayDatePicker,
		pick
	) => (
		<div>
			<Button
				onClick={ () => {
					setShowDatePicker( true );
				} }
				className="datepicker"
				data-testid={ 'start-date-button' }
			>
				{ label }
			</Button>
			{ shouldDisplayDatePicker && (
				<DatePicker currentDate={ date } onChange={ pick } />
			) }
		</div>
	);

	return (
		<PluginDocumentSettingPanel
			name="sensei-wcpc-course-access-period"
			title={ __( 'Access Period', 'sensei-pro' ) }
			className="sensei-wcpc-course-expiration"
		>
			<p className="sensei-wcpc-course-expiration__intro">
				{ __(
					'Set a timeframe that students will have access to this course.',
					'sensei-pro'
				) }
			</p>
			<div className="access-period-starts">
				<SelectControl
					label={ __( 'Course Access Starts', 'sensei-pro' ) }
					value={ startType }
					options={ [
						{
							label: __( 'Immediately', 'sensei-pro' ),
							value: IMMEDIATELY,
						},
						{
							label: __( 'On a specific date', 'sensei-pro' ),
							value: STARTS_ON,
						},
					] }
					onChange={ setStartType }
				/>
				{ STARTS_ON === startType &&
					accessPeriodDatePicker(
						setShowStartDatePicker,
						startDate,
						startDate
							? sprintf(
									/* translators: %s is replaced with start date string in format YYYY-MM-DD */
									__( 'Starts on %s', 'sensei-pro' ),
									format( startDate )
							  )
							: startCourseAccessPeriodLabel,
						showStartDatePicker,
						pickStartDate
					) }
			</div>
			<div className="access-period-expires">
				<SelectControl
					label={ __( 'Course Access Ends', 'sensei-pro' ) }
					value={ expirationType }
					options={ [
						{
							label: __( 'Never', 'sensei-pro' ),
							value: NO_EXPIRATION,
						},
						{
							label: __( 'After a set period', 'sensei-pro' ),
							value: EXPIRES_AFTER,
						},
						{
							label: __( 'On a specific date', 'sensei-pro' ),
							value: EXPIRES_ON,
						},
					] }
					onChange={ setExpirationType }
				/>

				{ EXPIRES_AFTER === expirationType && expiresAfterForm }
				{ EXPIRES_ON === expirationType &&
					accessPeriodDatePicker(
						setShowExpirationDatePicker,
						expirationDate,
						expirationDate
							? sprintf(
									/* translators: %s is replaced with start date string in format YYYY-MM-DD */
									__( 'Expires on %s', 'sensei-pro' ),
									format( expirationDate )
							  )
							: expiresCourseAccessPeriodLabel,
						showExpirationDatePicker,
						pickExpirationDate
					) }
			</div>
		</PluginDocumentSettingPanel>
	);
};

export default CourseExpirationSidebar;
