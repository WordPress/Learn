import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockVariation( 'core/query', {
	name: 'wporg/activity-kits',
	title: __( 'Activity Kits', 'wporg-learn' ),
	description: __( 'Display a filterable grid of activity kits.', 'wporg-learn' ),
	isActive: [ 'namespace' ],
	attributes: {
		namespace: 'wporg/activity-kits',
		query: {
			postType: 'activity_kit',
			perPage: 12,
			order: 'desc',
			orderBy: 'date',
		},
	},
	innerBlocks: [
		[ 'core/post-template', {}, [ [ 'wporg/activity-kit-card', {} ] ] ],
		[ 'core/query-pagination', {} ],
	],
	scope: [ 'inserter' ],
} );
