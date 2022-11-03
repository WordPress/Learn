/**
 * Internal dependencies
 */
import meta from './block.json';
import edit from './timeline-edit';
import save from './timeline-save';
import { ReactComponent as icon } from '../../icons/interactive-video-block.svg';

/**
 * Timeline Block definition.
 */
const timelineBlock = {
	...meta,
	icon,
	edit,
	save,
};

export default timelineBlock;
