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
import './question/question-block/question-frontend';

runBlocks();
