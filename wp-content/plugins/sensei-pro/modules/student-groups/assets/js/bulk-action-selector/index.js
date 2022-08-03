/**
 * WordPress dependencies
 */
import { Button, SelectControl } from '@wordpress/components';
import { useCallback, useState, useMemo } from '@wordpress/element';

import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import './style.scss';

const BulkActionSelector = ( { options, onApply, placeholder, disabled } ) => {
	const [ value, setValue ] = useState( '' );
	const handleApply = useCallback( () => onApply( value ), [ value ] );

	const selectOptions = useMemo(
		() => [
			{ id: 'placeholder', label: placeholder, value: '' },
			...options,
		],
		[ options ]
	);
	return (
		<div className="bulk-action-selector">
			<SelectControl
				value={ value }
				options={ selectOptions }
				onChange={ setValue }
				className={ 'bulk-action-selector__select' }
			/>

			<Button
				onClick={ handleApply }
				className="button bulk-action-selector__button"
				disabled={ value === '' || disabled }
			>
				{ __( 'Apply', 'sensei-pro' ) }
			</Button>
		</div>
	);
};

export default BulkActionSelector;
