/**
 * External dependencies
 */
import { debounce } from 'lodash';
import Select, { components } from 'react-select';
/**
 * WordPress dependencies
 */
import { Icon, search as searchIcon } from '@wordpress/icons';
import { useState, useCallback, useRef, useEffect } from '@wordpress/element';
import { TextHighlight } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

const OptionWithHighlight = ( search ) => ( props ) => {
	return (
		<components.Option { ...props }>
			<TextHighlight text={ props.label } highlight={ search }>
				{ props.label }
			</TextHighlight>
		</components.Option>
	);
};

/**
 * Callback called when the new value should be use to get the new list of options
 * It is debounced using 250ms
 *
 * @callback onSearch
 * @param {string} term - The value searched by the user on the input field
 */

/**
 * Callback called when a new value is selected on the list of options
 *
 * @callback onSelect
 * @param {Object} selected - The selected option
 */

/**
 * Callback called it forwards the input onBlur event
 *
 * @callback onBlur
 * @param {Object} event - Event object
 */

/**
 * Represents one item in the list of available option.
 *
 * @typedef {Object} Option
 * @property {string}        label - Value to be displayed on the list of options
 * @property {string|number} value - Indicate the id that will be used to identify the option
 */

/**
 * Component to search and select a item in a list of items
 *
 * @typedef {Object} Pros
 * @property {onSearch}      onSearch    - It should be used to fill the options prop.
 * @property {onSelect}      onSelect    - It should be used to get the selected value.
 * @property {onBlur}        onBlur      - Called when there is onBlur event on the input
 * @property {Array(Option)} options     - List of available options that should be displayed when the user type a value
 * @property {boolean}       isLoading   - Display the component loading state
 * @property {boolean}       isDisabled  - Enable/Disable the component
 * @property {string}        placeholder - Display an instruction message
 */

const SearchAutoComplete = ( {
	onSearch,
	onSelect,
	onBlur,
	options,
	isLoading,
	placeholder,
	disabled,
	maxMenuHeight = 130,
} ) => {
	const [ value ] = useState( null );
	const [ inputValue, setInputValue ] = useState( '' );

	// Keep mounted status to avoid state updates on unmounted components.
	const isMounted = useRef( true );
	useEffect( () => {
		return () => {
			isMounted.current = false;
		};
	}, [] );

	// eslint-disable-next-line react-hooks/exhaustive-deps
	const handleSearch = useCallback(
		debounce( ( term ) => {
			if ( isMounted.current ) {
				setInputValue( term );
				onSearch( term );
			}
		}, 250 ),
		[ onSearch ]
	);

	const handleChange = useCallback(
		( selected ) => {
			onSelect( selected );
		},
		[ onSelect ]
	);

	const handleBlur = ( event ) => {
		// Important because the input onBlur event is bubbling to the modals component, closing it unexpectedly.
		event.stopPropagation();

		// Reset search and input value.
		if ( inputValue !== '' ) {
			setInputValue( '' );
			onSearch( '' );
		}

		if ( onBlur ) onBlur();
	};

	return (
		<div className="search-autocomplete">
			<Icon icon={ searchIcon } className="search-autocomplete__icon" />
			<Select
				isLoading={ isLoading }
				onInputChange={ handleSearch }
				placeholder={ placeholder }
				defaultMenuIsOpen={ false }
				value={ value }
				components={ {
					Option: OptionWithHighlight( inputValue ),
				} }
				isDisabled={ disabled }
				styles={ {
					placeholder: ( provided ) => ( {
						...provided,
						marginLeft: '33px',
					} ),
					input: ( provided ) => ( {
						...provided,
						marginLeft: '33px',
						height: '36px',
					} ),
				} }
				onBlur={ handleBlur }
				blurInputOnSelect={ true }
				onChange={ handleChange }
				options={ options }
				maxMenuHeight={ maxMenuHeight }
			></Select>
		</div>
	);
};

export default SearchAutoComplete;
