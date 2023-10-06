/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import TutorAiBlock from './tutor-ai-block';
import AnswerBlock from './answer-block';
import StudentAnswerBlock from './student-text-block';

const blocks = [ TutorAiBlock, AnswerBlock, StudentAnswerBlock ];
blocks.forEach( ( block ) => registerBlockType( block.name, block ) );
