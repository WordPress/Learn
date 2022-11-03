/**
 * External dependencies
 */
import roundWithDecimals from 'sensei/assets/shared/helpers/player/round-with-decimals.js';

/**
 * WordPress dependencies
 */
import { useEffect, useState, forwardRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Just parse a given string to integer.
 *
 * @param {string} value The string to be converted
 * @return {number} The converted number.
 */
const toInt = ( value ) => parseInt( value, 10 );

/**
 * Return a number as a string, padded by zeros.
 *
 * @param {number} value The number to convert and add padding.
 * @param {number} index The index of the tme part being processed.
 * @return {string} The string representation.
 */
const toTimePart = ( value, index ) => {
	const str =
		'' + ( index === 3 ? Math.round( value ) : Math.floor( value ) );
	return str.padStart( 2, '0' );
};

/**
 * Verify if a value in an array of [hours, minutes, seconds, milliseconds]
 * is invalid. To be used with Array's some method.
 *
 * @param {string} value The value being processed.
 * @param {number} index The index of the value being processed in the array.
 * @return {boolean} If the value is invalid or not.
 */
const isPartInvalid = ( value, index ) => {
	const { length } = value;
	return length < 2 || ( index > 0 && index < 3 && 60 <= toInt( value ) );
};

/**
 * Return time for a given array of time parts with 4 parts,
 * in the format [hours, minutes, seconds, milliseconds]. Should be
 * used with Array's reduce method.
 *
 * @param {number} sum   The current result.
 * @param {number} value The value of the current item of the array.
 * @param {number} index The index of the current value in the array.
 * @return {number} The total number of seconds.
 */
const sumParts = ( sum, value, index ) =>
	sum + ( index < 3 ? Math.pow( 60, 2 - index ) * value : value / 100 );

/**
 * Converts a given time, in seconds, to an array containing strings representing
 * each part of the time saved.
 *
 * @param {number} time Time to convert, in seconds.
 * @return {string[]} An array in the format [hours, minutes, seconds, milliseconds].
 */
const convertTimeToArray = ( time ) => {
	const minutePart = time / 60;
	return [
		minutePart / 60,
		minutePart % 60,
		time % 60,
		( time % 1 ) * 100,
	].map( toTimePart );
};

/**
 * Component of the field to edit time of the Break Point.
 *
 * @param {Object}   props               Component Props
 * @param {number}   props.duration      The video duration. In Seconds.
 * @param {number}   props.time          The current position of the break point, in seconds.
 * @param {Function} props.setAttributes Function to update the attributes of the break point.
 * @param {Object}   ref                 Component ref.
 */
const EditTime = forwardRef( ( { duration, time, setAttributes }, ref ) => {
	const [ value, setValue ] = useState( [ '', '', '', '' ] );
	const videoLongerThanOneHour = duration >= 60 * 60;

	useEffect( () => {
		setValue( convertTimeToArray( time ) );
	}, [ time ] );

	useEffect( () => {
		if ( value.some( isPartInvalid ) || ! duration ) {
			return;
		}
		const newValue = value.map( toInt ).reduce( sumParts, 0 );

		// It prevents JS math issues with decimals.
		const roundedValue = roundWithDecimals( newValue, 3 );

		setAttributes( {
			time: Math.min( roundedValue, duration ),
		} );
	}, [ value, duration, setAttributes ] );

	const onInput = ( index ) => ( e ) => {
		const newValue = toTimePart(
			e.target.value.replace( /\D/g, '' ),
			index
		);
		setValue( ( oldState ) => {
			const newState = oldState.slice();
			newState[ index ] = newValue;
			return newState;
		} );
	};

	const onChange = () => {
		if ( value.some( isPartInvalid ) ) {
			setValue( convertTimeToArray( time ) );
			return;
		}
		const newValue = value.map( toInt ).reduce( sumParts, 0 );
		const newTime = Math.min( newValue, duration );
		if ( newTime === duration ) {
			const maxTime = convertTimeToArray( newTime );
			setValue( maxTime );
		}
	};

	const onKeyDown = ( e ) => {
		e.stopPropagation();
	};

	return (
		/* eslint-disable-next-line jsx-a11y/label-has-for */
		<label className="sensei-pro-break-point-edit-time" ref={ ref }>
			<span className="sensei-pro-break-point-edit-time__label">
				{ __( 'Time', 'sensei-pro' ) }
			</span>
			<div
				className="sensei-pro-break-point-edit-time__input-container"
				onBlur={ onChange }
			>
				{ videoLongerThanOneHour ? (
					<>
						<input
							className="sensei-pro-break-point-edit-time__input"
							type="number"
							aria-label={ __(
								'Hour of the Break Point in the video',
								'sensei-pro'
							) }
							value={ value[ 0 ] }
							min={ 0 }
							onKeyDown={ onKeyDown }
							onInput={ onInput( 0 ) }
						/>
						:
					</>
				) : null }
				<input
					type="number"
					className="sensei-pro-break-point-edit-time__input"
					aria-label={ __(
						'Minute of the Break Point in the video',
						'sensei-pro'
					) }
					value={ value[ 1 ] }
					max={ 59 }
					min={ 0 }
					onInput={ onInput( 1 ) }
					onKeyDown={ onKeyDown }
				/>
				:
				<input
					type="number"
					className="sensei-pro-break-point-edit-time__input"
					aria-label={ __(
						'Second of the Break Point in the video',
						'sensei-pro'
					) }
					value={ value[ 2 ] }
					max={ 59 }
					min={ 0 }
					onInput={ onInput( 2 ) }
					onKeyDown={ onKeyDown }
				/>
				.
				<input
					type="number"
					className="sensei-pro-break-point-edit-time__input"
					aria-label={ __(
						'Milliseconds of the Break Point in the video',
						'sensei-pro'
					) }
					value={ value[ 3 ] }
					max={ 99 }
					min={ 0 }
					onInput={ onInput( 3 ) }
					onKeyDown={ onKeyDown }
				/>
			</div>
		</label>
	);
} );

export default EditTime;
