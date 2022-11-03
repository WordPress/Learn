/**
 * Internal dependencies
 */
import { runBlocks } from './shared/block-frontend';
import './required-blocks';

/**
 * Blocks
 */
import './flashcard-block/frontend';
import './hotspots-block/frontend';
import './tasklist-block/frontend';
import './interactive-video/interactive-video-block/interactive-video-frontend';
import './question/question-block/question-frontend';
import './interactive-video/break-point-block/break-point-frontend';
import './interactive-video/timeline-block/timeline-frontend';

runBlocks();
