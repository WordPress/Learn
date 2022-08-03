/**
 * WordPress dependencies
 */
import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { render, useCallback, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
/**
 * Internal dependencies
 */
import useLazyRequest from '../hooks/use-lazy-request';
import { create } from '../services/groups';

import './style.scss';

const GroupCreationModal = ( { onClose } ) => {
	const [ showModal, setShowModal ] = useState( false );
	const [ groupName, setGroupName ] = useState( '' );

	const {
		run: runCreateGroup,
		isLoading: isSending,
		error,
		response,
		hasError,
	} = useLazyRequest( create );

	const handleClose = ( needsReload ) => {
		setShowModal( false );
		if ( onClose ) {
			onClose( needsReload );
		}
	};

	const handleChange = ( name ) => setGroupName( name );

	const handleClick = useCallback( () => {
		setShowModal( true );
	}, [ setShowModal ] );

	if ( response && ! hasError ) {
		handleClose( true );
	}

	return (
		<>
			<Button onClick={ handleClick } variant="secondary">
				{ __( 'New Group', 'sensei-pro' ) }
			</Button>
			{ showModal && (
				<Modal
					className="sensei-group-creation-modal"
					title={ __( 'Create A New Group', 'sensei-pro' ) }
					onRequestClose={ () => handleClose() }
				>
					{ error && (
						<Notice
							status="error"
							isDismissible={ false }
							className="sensei-group-creation-modal__notice"
						>
							{ error?.message ||
								__(
									'Something went wrong. Please try again.',
									'sensei-pro'
								) }
						</Notice>
					) }
					<div className="sensei-group-creation-modal_description">
						{ __(
							'Give the new group a name. Keep it short and make it unique to help you identify it in the future. You can edit the group name later.',
							'sensei-pro'
						) }
					</div>
					<div className="sensei-group-creation-modal_input-container">
						<label htmlFor="group-name">
							{ __( 'Group Name', 'sensei-pro' ) }
						</label>
						<input
							type="text"
							placeholder={ __(
								'Enter your group name',
								'sensei-pro'
							) }
							value={ groupName }
							onChange={ ( event ) =>
								handleChange( event.target.value )
							}
							id="group-name"
						/>
					</div>
					<div className="sensei-group-creation-modal_action">
						<Button
							variant="primary"
							disabled={ ! groupName || isSending }
							onClick={ () => runCreateGroup( groupName ) }
						>
							{ isSending && <Spinner /> }
							{ __( 'Create Group', 'sensei-pro' ) }
						</Button>
					</div>
				</Modal>
			) }
		</>
	);
};

domReady( () => {
	const creationModalContainer = document.getElementById(
		'group-add-button'
	);

	if ( creationModalContainer ) {
		render(
			<GroupCreationModal
				onClose={ ( needsReload ) =>
					needsReload && window.location.reload()
				}
			/>,
			creationModalContainer
		);
	}
} );

export default GroupCreationModal;
