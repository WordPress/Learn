/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import DocumentSettings from './showcase-document-settings';
import ShowcaseTermsConditionsPanel from './showcase-terms-conditions-panel';
import edit from './showcase-edit';
import metadata from './block.json';

registerBlockType( metadata, {
	edit,
} );

registerPlugin( 'sensei-pro-showcase-plugin', {
	render: () => <DocumentSettings />,
	icon: null,
} );

registerPlugin( 'sensei-pro-showcase-tos-pre-publish-panel', {
	render: ShowcaseTermsConditionsPanel,
} );
