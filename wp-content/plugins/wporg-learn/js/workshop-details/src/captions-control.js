import { FormTokenField } from '@wordpress/components';

const CaptionsControl = ( { label, tokens, options, onChange } ) => (
	<FormTokenField
		value={ tokens }
		suggestions={ options.map( ( i ) => i.label ) }
		onChange={ onChange }
		placeholder="Search Languages"
		label={ label }
	/>
);

export default CaptionsControl;
