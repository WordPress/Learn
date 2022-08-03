/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { HotspotBlock } from './hotspot-block';
import { ImageHotspotsBlock } from './image-hotspots-block';

const blocks = [ ImageHotspotsBlock, HotspotBlock ];
blocks.forEach( ( block ) => registerBlockType( block.name, block ) );
