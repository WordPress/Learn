/**
 * External dependencies
 */
import Select, { components } from 'react-select';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useCallback, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/components';
import { chevronDown } from '@wordpress/icons';

const LOADING_INDICATOR_VALUE = -1;
const LOADING_INDICATOR_OPTION = { label: '', value: LOADING_INDICATOR_VALUE };

/**
 * Internal dependencies
 */
import { optionsMap } from './options';

/**
 * Renders group selection.
 *
 * @param {Object} props
 */
export const Groups = ( props ) => {
	const { onChange, attributes } = props;
	const [ options, setOptions ] = useState( [ LOADING_INDICATOR_OPTION ] );
	const [ offset, setOffset ] = useState( 0 );

	const groupsRecords = useSelect(
		( select ) => {
			// Prepare groups query params.
			const entityRecordsParams = [
				'postType',
				'group',
				{
					orderby: 'title',
					order: 'asc',
					per_page: 100,
					offset,
				},
			];

			// Get the list of group entities for the query.
			const records = select( 'core' ).getEntityRecords(
				...entityRecordsParams
			);

			// Check if we recieved the records for the query.
			const hasRecords = select( 'core' ).hasEntityRecords(
				...entityRecordsParams
			);

			return {
				records: Array.isArray( records ) ? records : [],
				offset,
				hasRecords,
			};
		},

		// Fetch new list of entities each time the offset changes.
		[ offset ]
	);

	// Update the list of options each time there is a new list of groups.
	useEffect( () => {
		if (
			groupsRecords.offset !== offset ||
			! groupsRecords.records.length
		) {
			return;
		}

		setOptions( ( oldOptions ) => {
			// We want the last option be a placehoder for loading indicator.
			// But each time we append new set of options we need to remove the
			// placeholder first.
			if (
				oldOptions.length &&
				oldOptions[ oldOptions.length - 1 ].value ===
					LOADING_INDICATOR_VALUE
			) {
				oldOptions.pop();
			}

			// Append new set of groups after the existing ones and
			// also put a loading indicator placeholder at the end.
			return [
				...oldOptions,
				...groupsRecords.records.map( ( record ) => ( {
					label: record.title.rendered,
					value: record.id,
				} ) ),
				LOADING_INDICATOR_OPTION,
			];
		} );
	}, [ groupsRecords, offset ] );

	// Update the offset when user scrolls to the bottom of select optoins.
	const handleMenuScrollToBottom = useCallback( () => {
		setOffset( options.length );
	}, [ options.length ] );

	const reachedEnd =
		! groupsRecords.records.length && groupsRecords.hasRecords;

	// We overwrite the Option component to render a loading indicator
	// at the end of the list of options.
	const Option = useCallback(
		( optionProps ) => {
			if ( optionProps.value === LOADING_INDICATOR_VALUE ) {
				// We don't show loading indicator if there is nothing more to load.
				if ( reachedEnd ) {
					return null;
				}

				return (
					<div
						style={ {
							...optionProps.getStyles( 'option', optionProps ),
						} }
					>
						<components.LoadingIndicator { ...optionProps } />
					</div>
				);
			}

			return <components.Option { ...optionProps } />;
		},
		[ reachedEnd ]
	);

	const handleGroupsChange = useCallback(
		( groups = [] ) => {
			onChange( { GROUPS: groups.length ? { groups } : undefined } );
		},
		[ onChange, attributes.senseiVisibility ]
	);

	// Visibility option description.
	const description = optionsMap.GROUPS?.description || '';

	return (
		<div className="sensei-block-visibility__option">
			<p className="sensei-block-visibility__option-title">
				{ __( 'Groups', 'sensei-pro' ) }
			</p>
			<Select
				options={ options.length === 1 && reachedEnd ? [] : options }
				onChange={ handleGroupsChange }
				value={ attributes.senseiVisibility?.GROUPS?.groups || [] }
				placeholder={ __( 'Select Groups', 'sensei-pro' ) }
				isMulti
				isClearable={ false }
				className="sensei-block-visibility-group-select__container"
				classNamePrefix="sensei-block-visibility-group-select"
				components={ { DropdownIndicator, Option } }
				noOptionsMessage={ () =>
					__( 'No Student Groups available', 'sensei-pro' )
				}
				onMenuScrollToBottom={ handleMenuScrollToBottom }
				isOptionDisabled={ ( { value } ) =>
					value === LOADING_INDICATOR_VALUE
				}
			/>
			{ description && (
				<p className="sensei-block-visibility__option-description">
					{ description }
				</p>
			) }
		</div>
	);
};

const DropdownIndicator = () => {
	return (
		<Icon
			className="sensei-block-visibility-group-select__indicator"
			icon={ chevronDown }
		/>
	);
};
