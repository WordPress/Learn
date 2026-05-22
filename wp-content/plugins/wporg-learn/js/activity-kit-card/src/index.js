import { registerBlockType } from '@wordpress/blocks';
import Edit from '../../shared/dynamic-edit';
import metadata from '../block.json';

registerBlockType( metadata.name, {
	edit: Edit,
} );
