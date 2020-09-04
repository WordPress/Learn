import { FormTokenField } from '@wordpress/components';

const CaptionsControl = ( { tokens, options, onChange } ) => (
	<FormTokenField
		value={ tokens }
		suggestions={ options.map( ( i ) => i.label ) }
		onChange={ onChange }
		placeholder="Search Languages"
		label="Captions"
	/>
);

export default CaptionsControl;
