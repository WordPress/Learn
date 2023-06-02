/**
 * WordPress dependencies
 */
import { CheckboxControl, Spinner } from '@wordpress/components';
import { useCallback, useRef, useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { store as coreDataStore } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import useSelectWithDebounce from '../../react-hooks/use-select-with-debounce';

/**
 * Callback for select or unselect groupItem
 *
 * @callback onChangeEvent
 * @param {boolean} isSelected Describes if the group was selected or unselected
 * @param {boolean} group      Group related to the triggered event
 */

/**
 * Loading group list component.
 */
const LoadingGroupList = () => (
	<li className="sensei-student-modal__course-list--loading">
		<Spinner />
	</li>
);

/**
 * Empty group list component.
 */
const EmptyGroupList = () => (
	<li className="sensei-student-modal__course-list--empty">
		{ __( 'No groups found.', 'sensei-pro' ) }
	</li>
);

/**
 * Group item.
 *
 * @param {Object}        props
 * @param {Object}        props.group    Group
 * @param {boolean}       props.checked  Checkbox state
 * @param {onChangeEvent} props.onChange Event triggered when the a group is select/unselected
 */
const GroupItem = ( { group, checked = false, onChange } ) => {
	const groupId = group?.id;
	const title = decodeEntities( group?.title?.rendered );
	const [ isChecked, setIsChecked ] = useState( checked );

	const onSelectGroup = useCallback(
		( isSelected ) => {
			setIsChecked( isSelected );
			onChange( { isSelected, group } );
		},
		[ group, onChange ]
	);

	return (
		<li className="sensei-student-modal__course-list__item" key={ groupId }>
			<CheckboxControl
				id={ `group-${ groupId }` }
				title={ title }
				checked={ isChecked }
				onChange={ onSelectGroup }
			/>
			<label htmlFor={ `group-${ groupId }` } title={ title }>
				{ title }
			</label>
		</li>
	);
};

/**
 * Callback for GroupSelection
 *
 * @callback onGroupSelectionChange
 * @param {Array} selectedGroups List of selected groups
 */

/**
 * Group list.
 *
 * @param {Object}                 props
 * @param {string}                 props.searchQuery Group to search for.
 * @param {onGroupSelectionChange} props.onChange    Event triggered when a group is selected or unselected
 */
export const GroupList = ( { searchQuery, onChange } ) => {
	const selectedGroups = useRef( [] );

	const selectGroup = useCallback(
		( { isSelected, group } ) => {
			selectedGroups.current = isSelected
				? [ ...selectedGroups.current, group ]
				: selectedGroups.current.filter( ( c ) => c.id !== group.id );

			onChange( selectedGroups.current );
		},
		[ onChange ]
	);

	const { groups, isFetching } = useSelectWithDebounce(
		( select ) => {
			const store = select( coreDataStore );

			const query = {
				per_page: 100,
				search: searchQuery,
			};

			return {
				groups:
					store.getEntityRecords( 'postType', 'group', query ) || [],
				isFetching: ! store.hasFinishedResolution( 'getEntityRecords', [
					'postType',
					'group',
					query,
				] ),
			};
		},
		[ searchQuery ],
		500
	);

	return (
		<>
			<span className="sensei-student-modal__course-list__header">
				{ __( 'Your Groups', 'sensei-lms' ) }
			</span>
			<ul className="sensei-student-modal__course-list">
				{ isFetching && <LoadingGroupList /> }

				{ ! isFetching && 0 === groups.length && <EmptyGroupList /> }

				{ ! isFetching &&
					0 < groups.length &&
					groups.map( ( group ) => (
						<GroupItem
							key={ group.id }
							group={ group }
							onChange={ selectGroup }
							checked={
								selectedGroups.current.length > 0 &&
								selectedGroups.current.find(
									( { id } ) => id === group.id
								)
							}
						/>
					) ) }
			</ul>
		</>
	);
};

export default GroupList;
