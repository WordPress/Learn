/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './style.scss';
import { ReactComponent as icon } from './icon.svg';
import edit from './group-members-list-edit';
import save from './group-members-list-save';
import metadata from './block.json';

registerBlockType( metadata, {
	icon,
	edit,
	save,
} );
