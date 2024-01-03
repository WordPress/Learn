/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { ReactComponent as icon } from './icon.svg';
import edit from './group-name-edit';
import save from './group-name-save';
import metadata from './block.json';

registerBlockType( metadata, {
	icon,
	edit,
	save,
} );
