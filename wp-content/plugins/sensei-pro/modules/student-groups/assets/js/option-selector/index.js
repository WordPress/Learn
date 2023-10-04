/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import SearchAutoComplete from '../search-autocomplete';
import SelectedOptions from '../selected-options';

const OptionsSelector = ( {
	selected = [],
	options = [],
	isLoading,
	onSearch,
	onChange = noop,
	onUnSelect = noop,
	placeholder = '',
	className = 'options-selector',
	disabled = false,
	excluded = [],
} ) => {
	const [ selectedOptions, setSelectedOptions ] = useState( selected );

	const handleSearch = useCallback(
		( term ) => {
			onSearch(
				term,
				selectedOptions.map( ( option ) => option.value )
			);
		},
		[ onSearch, selectedOptions ]
	);

	const handleSelect = useCallback(
		( option ) => {
			setSelectedOptions( ( state ) => {
				const updated = [ option, ...state ];
				onSearch(
					'',
					updated.map( ( t ) => t.value )
				);
				onChange( updated );

				return updated;
			} );
		},
		[ onSearch ]
	);

	const handleChange = useCallback(
		( option ) =>
			setSelectedOptions( ( state ) => {
				const filtered = state.filter(
					( item ) => item.value !== option.value
				);
				onChange( filtered );
				onUnSelect( option );
				return filtered;
			} ),
		[ onUnSelect ]
	);

	useEffect( () => {
		setSelectedOptions( ( previous ) =>
			previous.filter( ( option ) => ! excluded.includes( option.value ) )
		);
	}, [ JSON.stringify( excluded ), setSelectedOptions ] );

	return (
		<div className={ className }>
			<SearchAutoComplete
				isLoading={ isLoading }
				options={ options }
				onSearch={ handleSearch }
				placeholder={ placeholder }
				onSelect={ handleSelect }
				disabled={ disabled }
			/>

			<SelectedOptions
				options={ selectedOptions }
				onChange={ handleChange }
				className={ `${ className }__selected-option` }
				disabled={ disabled }
			/>
		</div>
	);
};

export default OptionsSelector;
