/**
 * Internal dependencies
 */
import metadata from './block.json';
import edit from './section-edit';
import save from './section-save';
/**
 * WordPress dependencies
 */
import { ReactComponent as icon } from '../../icons/accordion-section.svg';

export default {
	...metadata,
	edit,
	save,
	icon,
};
