/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './summary-edit';
import save from './summary-save';
/**
 * WordPress dependencies
 */
import { heading as icon } from '@wordpress/icons';

export default {
	...metadata,
	edit,
	save,
	icon,
};
