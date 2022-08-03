/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

export const handleFailedNoticeDismiss = async ( event ) => {
	if ( ! event.target.matches( '.notice-dismiss' ) ) {
		return;
	}

	try {
		const data = new FormData();
		data.append(
			'action',
			'sensei_pro_data_migrator_dismiss_failed_notice'
		);
		data.append(
			'_ajax_nonce',
			window.sensei_pro_data_migrator.dismiss_failed_notice_nonce
		);

		const response = await fetch( window.ajaxurl, {
			method: 'POST',
			body: data,
		} );

		if ( ! response.ok ) {
			throw Error( __( 'Failed to process the request.', 'sensei-pro' ) );
		}
	} catch ( error ) {
		const errorMessage = sprintf(
			/* translators: %s: Error message. */
			__( 'An error occurred: %s', 'sensei-pro' ),
			error.message
		);

		// eslint-disable-next-line no-alert
		alert( errorMessage );
	}
};
