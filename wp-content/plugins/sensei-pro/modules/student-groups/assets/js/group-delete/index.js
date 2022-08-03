/**
 * WordPress dependencies
 */
import { RawHTML, useState } from '@wordpress/element';
/**
 * Internal dependencies
 */
import ConfirmationDialog from '../confirmation-dialog';
import { __, sprintf } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
/**
 * External dependencies
 */
import { sanitize } from 'dompurify';
import apiFetch from '@wordpress/api-fetch';

const performGroupDelete = async (
	groupId,
	groupName,
	setError,
	setIsBusy
) => {
	try {
		await apiFetch( {
			path: `/sensei-pro-student-groups/v1/groups/${ groupId }`,
			method: 'DELETE',
		} );
		return true;
	} catch ( e ) {
		setError( e.message ?? __( 'Something Wrong', 'sensei-pro' ) );
	} finally {
		setIsBusy( false );
	}
	return false;
};

export const GroupDelete = ( { groupId, groupName, onComplete } ) => {
	const [ error, setError ] = useState( '' );
	const [ isBusy, setIsBusy ] = useState( false );

	return (
		<ConfirmationDialog
			showDialog={ true }
			okButtonText={ __( 'Delete Group', 'sensei-pro' ) }
			title={ __( 'Delete Group', 'sensei-pro' ) }
			onConfirm={ async () => {
				setIsBusy( true );
				if (
					await performGroupDelete(
						groupId,
						groupName,
						setError,
						setIsBusy
					)
				) {
					onComplete( true );
				}
			} }
			onCancel={ () => onComplete() }
			isBusy={ isBusy }
		>
			<div>
				{ error && (
					<Notice
						status="error"
						isDismissible={ false }
						className="sensei-group-delete-action__notice"
					>
						{ error }
					</Notice>
				) }
				<RawHTML>
					{ sprintf(
						// Translators: placeholder is the name of the group.
						__(
							'Are you sure you want to delete the group <strong>%s</strong>?',
							'sensei-pro'
						),
						sanitize( groupName )
					) }
				</RawHTML>
			</div>
		</ConfirmationDialog>
	);
};
