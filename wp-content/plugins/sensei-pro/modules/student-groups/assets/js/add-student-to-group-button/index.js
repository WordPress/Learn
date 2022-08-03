/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __ } from '@wordpress/i18n';
import { render, useState } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';

/**
 * Internal dependencies
 */
import AddStudentToGroup from '../add-student-to-group';
import './style.scss';

/**
 * Add student group button and modal.
 *
 * @param {Object} props
 * @param {string} props.groupId   The Group's id.
 * @param {string} props.groupName The Group name.
 * @param {string} props.isPrimary isPrimary flag for button.
 */
export const AddStudentToGroupButton = ( {
	groupId,
	groupName,
	isPrimary,
} ) => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );

	const closeModal = () => {
		setIsModalOpen( false );
	};

	const reload = ( needsReload ) => {
		if ( needsReload ) window.location.reload();
	};

	return (
		<div className="student-groups-custom-navigation">
			<Button
				onClick={ () => setIsModalOpen( true ) }
				isPrimary={ isPrimary }
				isSecondary={ ! isPrimary }
			>
				<div className="">{ __( 'Add Students', 'sensei-pro' ) }</div>
			</Button>
			{ isModalOpen && (
				<Modal
					onRequestClose={ closeModal }
					title={ __( 'Add Students', 'sensei-pro' ) }
					className="add-student-to-group-modal"
				>
					<AddStudentToGroup
						groupName={ groupName }
						groupId={ groupId }
						onComplete={ reload }
					/>
				</Modal>
			) }
		</div>
	);
};

domReady( () =>
	Array.from(
		document.getElementsByClassName( 'add-student-to-group-button' )
	).forEach( ( actionMenu ) => {
		render(
			<AddStudentToGroupButton { ...actionMenu?.dataset } />,
			actionMenu
		);
	} )
);
