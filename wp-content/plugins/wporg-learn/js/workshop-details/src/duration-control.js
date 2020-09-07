import { TextControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { getHours, getMinutes, getSeconds } from './utils';

const DurationControl = ( { duration, onChange } ) => {
	const [ hours, setHours ] = useState( getHours( duration ) );
	const [ minutes, setMinutes ] = useState( getMinutes( duration ) );
	const [ seconds, setSeconds ] = useState( getSeconds( duration ) );

	useEffect( () => {
		const hoursInSeconds = +hours * 3600;
		const minutesInSeconds = +minutes * 60;
		onChange( hoursInSeconds + minutesInSeconds + +seconds );
	}, [ hours, minutes, seconds ] );

	return (
		<>
			<TextControl
				label="Hours"
				value={ hours }
				onChange={ ( hours ) => setHours( hours ) }
				type="number"
				min="0"
			/>
			<TextControl
				label="Minutes"
				value={ minutes }
				onChange={ ( minutes ) => setMinutes( minutes ) }
				type="number"
				max="59"
				min="0"
			/>
			<TextControl
				label="Seconds"
				value={ seconds }
				onChange={ ( seconds ) => setSeconds( seconds ) }
				type="number"
				max="59"
				min="0"
			/>
		</>
	);
};

export default DurationControl;
