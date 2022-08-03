/**
 * WordPress dependencies
 */
import { DatePicker, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import './style.scss';

/**
 *
 * Date picker with reset button.
 *
 * @param {Function} resetDate Reset date function for datepicker.
 */
export const DatePickerWithReset = ( { resetDate, ...props } ) => {
	return (
		<div className="date-picker-with-reset__wrapper">
			<DatePicker { ...props } />
			<Button
				className="date-picker-with-reset__reset_button"
				onClick={ resetDate }
			>
				{ __( 'Reset', 'sensei-pro' ) }
			</Button>
		</div>
	);
};
