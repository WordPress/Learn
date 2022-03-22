/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies.
 */
import ProductTour from './product-tour';

registerPlugin( 'sensei-wc-paid-courses-product-tour-plugin', {
	render: ProductTour,
	icon: null,
} );
