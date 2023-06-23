/**
 * Internal dependencies
 */

import metadata from './block.json';
import edit from './accordion-edit';
import save from './accordion-save';

/**
 * WordPress dependencies
 */
import { ReactComponent as icon } from '../../icons/accordion.svg';

export default {
	...metadata,
	edit,
	save,
	icon,
};
