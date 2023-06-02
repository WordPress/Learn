/**
 * Internal dependencies
 */
import SummaryBlock from './summary-block';
import SectionBlock from './section-block';
import AccordionBlock from './accordion-block';
import ContentBlock from './content-block';
/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

const blocks = [ ContentBlock, SummaryBlock, SectionBlock, AccordionBlock ];
blocks.forEach( ( block ) => registerBlockType( block.name, block ) );
