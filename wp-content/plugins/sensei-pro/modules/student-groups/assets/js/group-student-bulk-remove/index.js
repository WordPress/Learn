/**
 * WordPress dependencies
 */
import { RawHTML, useState } from '@wordpress/element';
/**
 * Internal dependencies
 */
import ConfirmationDialog from '../confirmation-dialog';
import { __, sprintf } from '@wordpress/i18n';
import { CheckboxControl, Notice } from '@wordpress/components';
/**
 * External dependencies
 */
import { sanitize } from 'dompurify';
import { removeAll } from '../services/group-students';
import useLazyRequest from '../hooks/use-lazy-request';

const GroupStudentBulkRemove = ( { groupId, studentIds, onCancel } ) => {
	const {
		run: runRemoveAll,
		isLoading,
		error,
		response,
	} = useLazyRequest( removeAll, [ groupId ] );

	const [ removeEnrolments, setRemoveEnrolments ] = useState( false );

	if ( response ) {
		window.location.reload();
	}

	return (
		<ConfirmationDialog
			showDialog={ true }
			okButtonText={ __( 'Remove Student(s)', 'sensei-pro' ) }
			title={ __( 'Confirm Remove Student(s)', 'sensei-pro' ) }
			onConfirm={ () =>
				runRemoveAll( groupId, studentIds, removeEnrolments )
			}
			onCancel={ onCancel }
			isBusy={ isLoading }
		>
			<div className="sensei-group-student-remove-confirmation-dialog-content">
				{ error && (
					<Notice
						status="error"
						isDismissible={ false }
						className="sensei-group-student-action__notice"
					>
						{ error.message ??
							__( 'Something Wrong', 'sensei-pro' ) }
					</Notice>
				) }
				<RawHTML>
					{ sprintf(
						// Translators: placeholder is the amount of students
						__(
							'Are you sure you want to remove <strong>%s</strong> student(s) from the group?',
							'sensei-pro'
						),
						sanitize( studentIds.length )
					) }
				</RawHTML>
				<CheckboxControl
					label={ __(
						'Also remove enrolments in group courses if there is any',
						'sensei-pro'
					) }
					onChange={ setRemoveEnrolments }
					checked={ removeEnrolments }
				></CheckboxControl>
			</div>
		</ConfirmationDialog>
	);
};

export default GroupStudentBulkRemove;
