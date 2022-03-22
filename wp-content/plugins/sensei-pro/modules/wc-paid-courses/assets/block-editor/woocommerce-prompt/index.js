/**
 * WordPress dependencies.
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies.
 */
import Sidebar from './sidebar';

registerPlugin( 'sensei-pro-woocommerce-prompt-sidebar', {
	render: Sidebar,
	icon: null,
} );
