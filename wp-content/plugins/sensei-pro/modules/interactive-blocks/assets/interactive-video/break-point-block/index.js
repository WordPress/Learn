/**
 * Internal dependencies
 */
import meta from './block.json';
import edit from './break-point-edit';
import save from './break-point-save';
import { ReactComponent as icon } from '../../icons/interactive-video-break-point.svg';

/**
 * Break Point Block definition.
 */
const breakPointBlock = {
	...meta,
	icon,
	edit,
	save,
};

export default breakPointBlock;
