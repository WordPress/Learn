/**
 * WordPress dependencies
 */
import { Button, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Basic generic confirmation dialog.
 *
 * @param {Object}      props
 * @param {string}      props.title            Title of the dialog.
 * @param {Function}    props.onConfirm        Callback function for Ok button click.
 * @param {Function}    props.onCancel         Callback function for Cancel button click.
 * @param {string}      props.okButtonText     Title of the dialog.
 * @param {string}      props.cancelButtonText Title of the dialog.
 * @param {boolean}     props.showDialog       Title of the dialog.
 * @param {boolean}     props.isBusy           Title of the dialog.
 * @param {JSX.Element} props.children         Inner content of dialog box.
 */
const ConfirmationDialog = ( {
	title,
	onConfirm,
	onCancel,
	okButtonText = __( 'Ok', 'sensei-pro' ),
	cancelButtonText = __( 'Cancel', 'sensei-pro' ),
	showDialog,
	isBusy = false,
	children,
} ) => {
	return (
		showDialog && (
			<Modal
				className="sensei-confirmation-dialog"
				title={ title }
				onRequestClose={ onCancel }
			>
				{ children }
				<div className="sensei-confirmation-dialog__actions">
					<Button
						text={ cancelButtonText }
						onClick={ onCancel }
						isBusy={ isBusy }
						disabled={ isBusy }
						className="btn info"
					/>
					<Button
						text={ okButtonText }
						onClick={ onConfirm }
						isBusy={ isBusy }
						disabled={ isBusy }
						isDestructive={ true }
					/>
				</div>
			</Modal>
		)
	);
};

export default ConfirmationDialog;
