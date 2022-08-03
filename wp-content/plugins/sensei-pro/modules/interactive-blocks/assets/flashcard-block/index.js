/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { CardBlock } from './card-block';

const blocks = [ CardBlock ];
blocks.forEach( ( block ) => registerBlockType( block.name, block ) );
