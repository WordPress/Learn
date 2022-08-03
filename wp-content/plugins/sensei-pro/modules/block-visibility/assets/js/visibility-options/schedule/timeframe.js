/**
 * External dependencies
 */
import moment from 'moment';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { Button, Dropdown, DateTimePicker } from '@wordpress/components';
import { calendar } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { optionsMap } from '../options';

export const Timeframe = ( { attributes, onChange } ) => {
	const { startDate, endDate } = attributes.senseiVisibility?.SCHEDULE || {};

	const startDateLabel = startDate
		? moment( startDate ).format( 'D MMM YYYY [at] hh:mm a' )
		: __( 'Start date', 'sensei-pro' );

	const stopDateLabel = endDate
		? moment( endDate ).format( 'D MMM YYYY [at] hh:mm a' )
		: __( 'Stop date', 'sensei-pro' );

	const handleDateChange = ( onClose, dateName ) => ( value ) => {
		if ( ! value ) {
			onClose();
		}
		const date = Date.parse( value );
		const schedule = attributes.senseiVisibility?.SCHEDULE || {};
		if ( isNaN( date ) ) {
			delete schedule[ dateName ];
		} else {
			schedule[ dateName ] = date;
		}
		onChange( { SCHEDULE: isEmpty( schedule ) ? undefined : schedule } );
	};

	const isInvalidStartDate = useCallback(
		( date ) => endDate && date.getTime() > endDate,
		[ endDate ]
	);

	const isInvalidEndDate = useCallback(
		( date ) => startDate && date.getTime() < startDate,
		[ startDate ]
	);

	// Visibility option description.
	const description = optionsMap.SCHEDULE?.description || '';

	return (
		<>
			<Dropdown
				renderToggle={ ( { onToggle } ) => (
					<Button
						className="sensei-block-visibility__schedule-date"
						icon={ calendar }
						onClick={ onToggle }
					>
						{ startDateLabel }
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<DateTimePicker
						is12Hour
						onChange={ handleDateChange( onClose, 'startDate' ) }
						currentDate={ startDate }
						isInvalidDate={ isInvalidStartDate }
					/>
				) }
			/>

			<Dropdown
				renderToggle={ ( { onToggle } ) => (
					<Button
						className="sensei-block-visibility__schedule-date sensei-block-visibility__schedule-date-end"
						icon={ calendar }
						onClick={ onToggle }
					>
						{ stopDateLabel }
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<DateTimePicker
						is12Hour
						onChange={ handleDateChange( onClose, 'endDate' ) }
						currentDate={ endDate }
						isInvalidDate={ isInvalidEndDate }
					/>
				) }
			/>
			{ description && (
				<p className="sensei-block-visibility__option-description">
					{ description }
				</p>
			) }
		</>
	);
};
