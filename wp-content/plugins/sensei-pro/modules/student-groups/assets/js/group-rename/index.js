/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Notice, Spinner } from '@wordpress/components';
/**
 * Internal dependencies
 */
import './style.scss';
import apiFetch from '@wordpress/api-fetch';

const ErrorMessage = ( { message } ) =>
	message ? (
		<Notice
			status="error"
			isDismissible={ false }
			className="sensei-group-name-update__notice"
		>
			{ message }
		</Notice>
	) : (
		<></>
	);

const UpdateGroupNameButton = ( { disabled, onClick, isSending } ) => {
	return (
		<Button
			variant="primary"
			disabled={ disabled || isSending }
			onClick={ onClick }
		>
			{ isSending && <Spinner /> }
			{ ! isSending && __( 'Save Name', 'sensei-pro' ) }
		</Button>
	);
};

/**
 * @typedef {Object} Props
 * @property {string}   groupName  The Group's current name to be populated in the text field.
 * @property {groupId}  groupId    The Group's id.
 * @property {Function} onComplete Called when the operation is completed successfully.
 */

/**
 * Component to edit the name of a group
 *
 * @param {Props} props The component Props.
 * @return {JSX} The component.
 */
const RenameGroup = ( { groupName, groupId, onComplete } ) => {
	const [ groupNewName, setGroupNewName ] = useState( groupName );
	const [ error, setError ] = useState( '' );
	const [ isSending, setIsSending ] = useState( false );

	const handleClose = ( needsReload ) => {
		if ( onComplete ) {
			onComplete( needsReload );
		}
	};
	const handleChange = ( name ) => {
		setGroupNewName( name );
		setError( '' );
	};
	const handleAction = async () => {
		try {
			setIsSending( true );
			await apiFetch( {
				path: '/sensei-pro-student-groups/v1/groups/' + groupId,
				method: 'POST',
				data: { name: groupNewName },
			} );
			handleClose( true );
		} catch ( e ) {
			setError( e.message ?? __( 'Something Wrong', 'sensei-pro' ) );
		} finally {
			setIsSending( false );
		}
	};

	return (
		<div className="sensei-group-name-update">
			<ErrorMessage message={ error }></ErrorMessage>
			<div className="sensei-group-name-update_input-container">
				<label htmlFor="group-name">
					{ __( 'Group Name', 'sensei-pro' ) }
				</label>
				<input
					type="text"
					placeholder={ __( 'Enter your group name', 'sensei-pro' ) }
					value={ groupNewName }
					onChange={ ( event ) => handleChange( event.target.value ) }
					id="group-name"
				/>
			</div>
			<div className="sensei-group-name-update__actions">
				<UpdateGroupNameButton
					onClick={ handleAction }
					disabled={ ! groupNewName }
					isSending={ isSending }
				/>
			</div>
		</div>
	);
};

export default RenameGroup;
