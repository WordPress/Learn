/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import StudentModal from './student-modal';

addFilter(
	'senseiStudentBulkActionModal',
	'sensei-pro',
	( defaultModal, action, closeModal, studentIds, studentName ) => {
		const supportedActions = [ 'addToGroup', 'removeFromGroup' ];
		if ( ! supportedActions.includes( action ) ) {
			return defaultModal;
		}

		return (
			<StudentModal
				action={ action }
				onClose={ closeModal }
				students={ studentIds }
				studentDisplayName={ studentName }
			/>
		);
	}
);

addFilter(
	'senseiStudentActionMenuControls',
	'sensei-pro',
	( defaultControls, setAction, setModalOpen ) => {
		defaultControls.push( {
			title: __( 'Add to Group', 'sensei-lms' ),
			onClick: () => {
				setAction( 'addToGroup' );
				setModalOpen( true );
			},
		} );
		defaultControls.push( {
			title: __( 'Remove from Group', 'sensei-lms' ),
			onClick: () => {
				setAction( 'removeFromGroup' );
				setModalOpen( true );
			},
		} );

		return defaultControls;
	}
);
