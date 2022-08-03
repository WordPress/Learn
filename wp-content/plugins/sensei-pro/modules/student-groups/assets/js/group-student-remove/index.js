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
import './style.scss';

/**
 * External dependencies
 */
import { sanitize } from 'dompurify';
import useLazyRequest from '../hooks/use-lazy-request';
import { remove } from '../services/group-students';

export const GroupStudentRemove = ( {
	groupId,
	studentId,
	studentName,
	onClose,
} ) => {
	const [ removeEnrolment, setRemoveEnrolment ] = useState( false );
	const {
		run: runRemove,
		response,
		isLoading: isBusy,
		error,
	} = useLazyRequest( remove );

	if ( response ) {
		window.location.reload();
	}

	return (
		<ConfirmationDialog
			showDialog={ true }
			okButtonText={ __( 'Remove Student', 'sensei-pro' ) }
			title={ __( 'Confirm Remove Student', 'sensei-pro' ) }
			onConfirm={ async () => {
				await runRemove( groupId, studentId, removeEnrolment );
			} }
			onCancel={ onClose }
			isBusy={ isBusy }
		>
			<div className="sensei-group-student-remove-confirmation-dialog-content">
				{ error && (
					<Notice
						status="error"
						isDismissible={ false }
						className="sensei-group-student-action__notice"
					>
						{ error?.message }
					</Notice>
				) }
				<RawHTML>
					{ sprintf(
						// Translators: placeholder is the student's name.
						__(
							'Are you sure you want to remove <strong>%s</strong> from the group?',
							'sensei-pro'
						),
						sanitize( studentName )
					) }
				</RawHTML>
				<CheckboxControl
					label={ __(
						'Also remove enrolments in group courses if there is any',
						'sensei-pro'
					) }
					onChange={ setRemoveEnrolment }
					checked={ removeEnrolment }
				></CheckboxControl>
			</div>
		</ConfirmationDialog>
	);
};
