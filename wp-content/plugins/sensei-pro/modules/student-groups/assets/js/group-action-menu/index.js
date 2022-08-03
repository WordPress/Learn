/**
 * WordPress dependencies
 */
import { DropdownMenu, Modal } from '@wordpress/components';
import { moreVertical } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { useState, useMemo, render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */

import AddStudentToGroup from '../add-student-to-group';
import './style.scss';
import RenameGroup from '../group-rename';
import { GroupDelete } from '../group-delete';

const redirectToSettingsPage = ( { groupSettingsUrl } ) => {
	window.location.assign( groupSettingsUrl );
};

const redirectToEditGroupStudentsPage = ( { editGroupStudentsUrl } ) => {
	window.location.assign( editGroupStudentsUrl );
};

const menuItems = [
	{
		id: 'add-to-group',
		title: __( 'Add Students', 'sensei-pro' ),
		Content: AddStudentToGroup,
		parent: Modal,
		onComplete: ( { needsReload, editGroupStudentsUrl } ) =>
			needsReload && window.location.assign( editGroupStudentsUrl ),
	},
	{
		id: 'edit-group-students',
		title: __( 'Edit Students', 'sensei-pro' ),
		redirect: redirectToEditGroupStudentsPage,
	},
	{
		id: 'set-access-period',
		title: __( 'Edit Course Access', 'sensei-pro' ),
		redirect: redirectToSettingsPage,
	},
	{
		id: 'rename-group',
		title: __( 'Edit Group Name', 'sensei-pro' ),
		Content: RenameGroup,
		parent: Modal,
	},
	{
		id: 'delete-group',
		title: __( 'Delete Group', 'sensei-pro' ),
		Content: GroupDelete,
		parent: ( { children } ) => <>{ children }</>,
	},
];

/**
 * Group action menu.
 *
 * @param {Object} props
 * @param {string} props.groupId              The Group's id.
 * @param {string} props.groupName            The Group's name.
 * @param {string} props.editGroupStudentsUrl The edit group students url.
 * @param {string} props.groupSettingsUrl     The edit group students url.
 */
export const GroupActionMenu = ( {
	groupId,
	groupName,
	editGroupStudentsUrl,
	groupSettingsUrl,
} ) => {
	const [ active, setActiveControl ] = useState( null );
	const isModalOpen = useMemo( () => active !== null, [ active ] );

	const toControl = ( item ) => ( {
		...item,
		onClick: () => setActiveControl( item ),
	} );

	const controls = useMemo( () => menuItems.map( toControl ), [
		setActiveControl,
		menuItems,
	] );

	const closeModal = () => {
		setActiveControl( null );
	};

	const reload = ( needsReload ) => {
		if ( active.onComplete ) {
			active.onComplete( { needsReload, editGroupStudentsUrl } );
		} else if ( needsReload ) window.location.reload();
		setActiveControl( null );
	};

	if ( active?.redirect ) {
		active.redirect( { editGroupStudentsUrl, groupSettingsUrl } );
		return null;
	}
	return (
		<>
			<DropdownMenu
				icon={ moreVertical }
				label={ __( 'Select an action', 'sensei-lms' ) }
				controls={ controls }
				className="groups-action-menu"
			/>

			{ isModalOpen && (
				<active.parent
					onRequestClose={ closeModal }
					title={ active.title }
				>
					<active.Content
						groupId={ groupId }
						groupName={ groupName }
						onComplete={ reload }
					/>
				</active.parent>
			) }
		</>
	);
};

domReady( () =>
	Array.from(
		document.getElementsByClassName( 'group-action-menu' )
	).forEach( ( actionMenu ) => {
		render( <GroupActionMenu { ...actionMenu?.dataset } />, actionMenu );
	} )
);
