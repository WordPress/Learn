/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Edit from './edit';
import './style.scss';

registerBlockType( 'wporg-learn/workshop-application-form', {
	title: __( 'Workshop Application Form', 'wporg-learn' ),
	description: __( 'Render a form for applying to present a workshop.', 'wporg-learn' ),
	category: 'widgets',
	icon: 'smiley',
	supports: {
		html: false,
	},
	edit: Edit,
	save: () => null,
} );
