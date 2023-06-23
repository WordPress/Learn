/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './content-edit';
import save from './content-save';
/**
 * WordPress dependencies
 */
import { group as icon } from '@wordpress/icons';

export default {
	...metadata,
	edit,
	save,
	icon,
};
