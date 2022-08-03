/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { noop } from 'lodash';
import moment from 'moment';

/**
 * Internal dependencies
 */
import { DatePickerWithReset } from '../date-picker-with-reset';
import './style.scss';

const accessPeriodLabel = __( 'Access Period', 'sensei-pro' );
const format = ( date ) => moment( date ).format( moment.HTML5_FMT.DATE );

/**
 * Access period selector.
 *
 * @type {Object} props Component props.
 * @type {Object} props.accessPeriod Access Period to be displayed.
 * @type {Date} props.accessPeriod.startDate Access Period start date.
 * @type {Date} props.accessPeriod.startDate Access Period end date.
 * @type {Object} props.onChange OnChange function for access period change.
 * @type {boolean} props.disabled Enable/disable the component
 *
 */
export const AccessPeriodSelector = ( {
	accessPeriod,
	onChange = noop,
	disabled,
} ) => {
	const wrapperRef = useRef( null );

	const [ startDate, setStartDate ] = useState(
		accessPeriod?.startDate || null
	);
	const [ endDate, setEndDate ] = useState( accessPeriod?.endDate || null );

	const [ showStartDatePicker, setShowStartDatePicker ] = useState( false );
	const [ showEndDatePicker, setShowEndDatePicker ] = useState( false );

	const openStartDatePicker = () => {
		setShowStartDatePicker( true );
		setShowEndDatePicker( false );
	};

	const openEndDatePicker = () => {
		setShowStartDatePicker( false );
		setShowEndDatePicker( true );
	};

	const pickStartDate = ( newDate ) => {
		setStartDate( format( newDate ) );
		setShowStartDatePicker( false );
		onChange( { startDate: format( newDate ), endDate } );
	};

	const pickEndDate = ( newDate ) => {
		setEndDate( format( newDate ) );
		setShowEndDatePicker( false );
		onChange( { startDate, endDate: format( newDate ) } );
	};

	const resetStartDate = () => {
		setStartDate( null );
		onChange( { startDate: null, endDate } );
		setShowStartDatePicker( false );
	};

	const resetEndDate = () => {
		setEndDate( null );
		setShowEndDatePicker( false );
		onChange( { startDate, endDate: null } );
	};

	const isStartDateInvalid = ( calendarDate ) => {
		const today = moment().startOf( 'day' );
		const current = moment( calendarDate ).startOf( 'day' );

		if ( current.isBefore( today ) ) {
			return true;
		}

		if ( endDate ) {
			return current.isAfter( endDate );
		}
		return false;
	};

	const isEndDateInvalid = ( calendarDate ) => {
		const today = moment().startOf( 'day' );
		const current = moment( calendarDate ).startOf( 'day' );

		if ( current.isBefore( today ) ) {
			return true;
		}

		if ( startDate ) {
			return current.isBefore( startDate );
		}
		return false;
	};

	const closeAll = ( event ) => {
		if (
			wrapperRef.current &&
			! wrapperRef.current.contains( event.target )
		) {
			setShowStartDatePicker( false );
			setShowEndDatePicker( false );
		}
	};
	useEffect( () => {
		document.addEventListener( 'mousedown', closeAll );
		return () => {
			document.removeEventListener( 'mousedown', closeAll );
		};
	}, [] );

	return (
		<div className="access-period-selector" ref={ wrapperRef }>
			<Button
				onClick={ openStartDatePicker }
				className="datepicker"
				data-testid={ 'start-date-button' }
				disabled={ disabled }
			>
				{ sprintf(
					/* translators: %s is replaced with start date string in format YYYY-MM-DD */
					__( 'Start %s', 'sensei-pro' ),
					startDate ? format( startDate ) : accessPeriodLabel
				) }
			</Button>

			<Button
				onClick={ openEndDatePicker }
				className="datepicker"
				data-testid={ 'end-date-button' }
				disabled={ disabled }
			>
				{ sprintf(
					/* translators: %s is replaced with end date string in format YYYY-MM-DD */
					__( 'End %s', 'sensei-pro' ),
					endDate ? format( endDate ) : accessPeriodLabel
				) }
			</Button>

			{ showStartDatePicker && (
				<DatePickerWithReset
					currentDate={ startDate }
					onChange={ pickStartDate }
					resetDate={ resetStartDate }
					isInvalidDate={ isStartDateInvalid }
				/>
			) }
			{ showEndDatePicker && (
				<DatePickerWithReset
					currentDate={ endDate }
					onChange={ pickEndDate }
					resetDate={ resetEndDate }
					isInvalidDate={ isEndDateInvalid }
				/>
			) }
		</div>
	);
};
