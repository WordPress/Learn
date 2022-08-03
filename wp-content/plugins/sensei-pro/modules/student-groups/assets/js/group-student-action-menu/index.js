/**
 * WordPress dependencies
 */
import { DropdownMenu } from '@wordpress/components';
import { moreVertical } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { useState, render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import './style.scss';
import { GroupStudentRemove } from '../group-student-remove';

const getMenuItems = ( groupId, studentId, studentName, setActiveControl ) => {
	return [
		{
			id: 'remove-student',
			title: __( 'Remove from Group', 'sensei-pro' ),
			content: GroupStudentRemove,
			props: {
				groupId,
				studentId,
				studentName,
				onClose: () => setActiveControl( null ),
			},
		},
	];
};
/**
 * Group action menu.
 *
 * @param {Object} props
 * @param {string} props.groupId     The Group's id.
 * @param {string} props.studentId   ID of the student.
 * @param {string} props.studentName Name of the student.
 */
export const GroupStudentActionMenu = ( {
	groupId,
	studentId,
	studentName,
} ) => {
	const [ activeControl, setActiveControl ] = useState( null );
	const menuItems = getMenuItems(
		groupId,
		studentId,
		studentName,
		setActiveControl
	).map( ( menuItem ) => {
		menuItem.onClick = () => {
			if ( menuItem.additionalOnClick ) {
				menuItem.additionalOnClick();
			}
			setActiveControl( menuItem );
		};
		return menuItem;
	} );

	return (
		<>
			<DropdownMenu
				icon={ moreVertical }
				label={ __( 'Select an action', 'sensei-lms' ) }
				controls={ menuItems }
				className="group-student-action-menu"
			/>
			{ activeControl && activeControl.content && (
				<activeControl.content { ...activeControl.props } />
			) }
		</>
	);
};

domReady( () =>
	Array.from(
		document.getElementsByClassName( 'group-student-action-menu' )
	).forEach( ( actionMenu ) => {
		render(
			<GroupStudentActionMenu { ...actionMenu?.dataset } />,
			actionMenu
		);
	} )
);
