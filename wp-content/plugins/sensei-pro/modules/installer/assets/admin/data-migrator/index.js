/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { handleFailedNoticeDismiss } from './failed-notice.js';

domReady( () => {
	const failedNotice = document.querySelector(
		'#sensei-pro-data-migrator-failed-notice'
	);

	failedNotice?.addEventListener( 'click', handleFailedNoticeDismiss );
} );
