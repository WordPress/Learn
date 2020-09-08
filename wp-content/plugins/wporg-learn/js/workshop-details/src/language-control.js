import { FormTokenField } from '@wordpress/components';

const LanguageControl = ( { label, tokens, options, onChange } ) => (
	<FormTokenField
		value={ tokens }
		suggestions={ options.map( ( i ) => i.label ) }
		onChange={ onChange }
		placeholder={ label }
		label={ label }
		maxLength={ 1 }
	/>
);

export default LanguageControl;
