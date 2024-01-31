/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockVariation } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import attributes from './attributes';

registerBlockVariation( 'core/button', {
	name: 'sensei-pro/join-group-button',
	title: __( 'Join Group Button', 'sensei-pro' ),
	description: __( 'Button to join the group.', 'sensei-pro' ),
	category: 'sensei-lms',
	attributes,
	isActive: ( blockAttributes, variationAttributes ) =>
		blockAttributes.className?.match( variationAttributes.className ) &&
		blockAttributes.url === variationAttributes.url,
	scope: [],
} );
