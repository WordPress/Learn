/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	Button,
	DatePicker,
	SelectControl,
	TextControl,
	PanelBody,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import moment from 'moment';
import editorLifecycle from 'sensei/assets/shared/helpers/editor-lifecycle';

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

let savedState;

/**
 * Resets the saved state.
 */
export const resetSavedState = () => {
	savedState = null;
};

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
	const [ expirationTypeMeta, setExpirationTypeMeta ] = useCourseMeta(
		EXPIRATION_TYPE
	);

	const [ expirationTypeInput, setExpirationTypeInput ] = useState(
		expirationTypeMeta
	);

	const [ startTypeMeta, setStartTypeMeta ] = useCourseMeta( START_TYPE );

	const [ startTypeInput, setStartTypeInput ] = useState( startTypeMeta );

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

	/**
	 * Save the current state.
	 */
	const saveCurrentState = () => {
		savedState = {
			expirationType: expirationTypeMeta,
			startType: startTypeMeta,
		};
	};

	if ( ! savedState ) {
		saveCurrentState();
	}

	// Save the current state on post save.
	useEffect( () => {
		editorLifecycle( {
			onSave: () => saveCurrentState,
		} );
	} );

	/**
	 * Validate and set the expiration type meta.
	 * If not valid, reset to saved state.
	 *
	 * @param {string} value
	 */
	const maybeSetExpirationTypeMeta = ( value ) => {
		if ( value === EXPIRES_ON && ! expirationDate ) {
			setExpirationTypeMeta( savedState.expirationType );
		} else {
			setExpirationTypeMeta( value );
		}
	};

	/**
	 * Validate and set the start type meta.
	 * If not valid, reset to saved state.
	 *
	 * @param {string} value
	 */
	const maybeSetStartTypeMeta = ( value ) => {
		if ( value === STARTS_ON && ! startDate ) {
			setStartTypeMeta( savedState.startType );
		} else {
			setStartTypeMeta( value );
		}
	};

	const onExpirationTypeInputChange = ( value ) => {
		setExpirationTypeInput( value );
		maybeSetExpirationTypeMeta( value );
	};

	const onStartTypeInputChange = ( value ) => {
		setStartTypeInput( value );
		maybeSetStartTypeMeta( value );
	};

	const pickExpirationDate = ( newDate ) => {
		setExpirationDate( newDate );
		setShowExpirationDatePicker( false );
		setExpirationTypeMeta( expirationTypeInput );
	};

	const pickStartDate = ( newDate ) => {
		setStartDate( newDate );
		setShowStartDatePicker( false );
		setStartTypeMeta( startTypeInput );
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
				data-testid={ 'set-date-button' }
			>
				{ label }
			</Button>
			{ shouldDisplayDatePicker && (
				<DatePicker currentDate={ date } onChange={ pick } />
			) }
		</div>
	);

	return (
		<PanelBody
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
					value={ startTypeInput }
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
					onChange={ onStartTypeInputChange }
					data-testid="access-period-starts"
				/>
				{ STARTS_ON === startTypeInput &&
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
					value={ expirationTypeInput }
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
					onChange={ onExpirationTypeInputChange }
					data-testid="access-period-expires"
				/>

				{ EXPIRES_AFTER === expirationTypeInput && expiresAfterForm }
				{ EXPIRES_ON === expirationTypeInput &&
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
		</PanelBody>
	);
};

export default CourseExpirationSidebar;
