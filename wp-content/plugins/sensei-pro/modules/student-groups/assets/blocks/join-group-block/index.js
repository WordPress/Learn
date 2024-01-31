/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './style.scss';
import { ReactComponent as icon } from './icon.svg';
import edit from './join-group-edit';
import save from './join-group-save';
import metadata from './block.json';

registerBlockType( metadata, {
	icon,
	edit,
	save,
} );
