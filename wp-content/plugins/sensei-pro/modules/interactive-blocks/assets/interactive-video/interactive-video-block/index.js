/**
 * Internal dependencies
 */
import meta from './block.json';
import variations from './variations';
import transforms from './transforms';
import edit from './interactive-video-edit';
import save from './interactive-video-save';
import { ReactComponent as icon } from '../../icons/interactive-video-block.svg';

export { default as addTransformButtonToVideoBlocks } from './add-transform-button-to-video-blocks';

/**
 * Interactive Video Block definition.
 */
const interactiveVideoBlock = {
	...meta,
	icon,
	variations,
	transforms,
	edit,
	save,
};

export default interactiveVideoBlock;
