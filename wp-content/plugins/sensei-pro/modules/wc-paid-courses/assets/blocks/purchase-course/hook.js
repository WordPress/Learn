/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import withPurchaseButton from './with-purchase-button';

/**
 * Switch Take Course Block to Purchase Course Block if course has products.
 *
 * @param {Object} settings Take Course block settings.
 */
const extendTakeCourseBlock = ( settings ) => {
	if ( 'sensei-lms/button-take-course' !== settings.name ) {
		return settings;
	}

	return {
		...settings,
		keywords: [
			...settings.keywords,
			__( 'Purchase', 'sensei-pro' ),
			__( 'Sell', 'sensei-pro' ),
		],
		edit: compose( withPurchaseButton )( settings.edit ),
	};
};

addFilter(
	'blocks.registerBlockType',
	'sensei-wc-paid-courses/purchase-course',
	extendTakeCourseBlock
);
