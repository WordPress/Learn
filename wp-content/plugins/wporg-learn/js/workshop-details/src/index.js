/*
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';


registerBlockType( 'wporg-learn/workshop-details', {
	title: __( 'Workshop Details', 'wporg-learn' ),
	description: __(
		'Show details about the workshop, pulled from post meta.',
		'wporg-learn'
	),
	category: 'widgets',
	icon: 'smiley',
	supports: {
		html: false,
	},

	edit: Edit,
	save: () => null,
} );
