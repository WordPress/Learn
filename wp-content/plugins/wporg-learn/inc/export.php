<?php

/**
 * Allow some raw data to be exposed in the REST API for certain post types, so that developers can import
 * a copy of production data for local testing.
 *
 * ⚠️ Be careful to only expose public data!
 */

namespace WPOrg_Learn\Export;

defined( 'WPINC' ) || die();

add_filter( 'wporg_export_context_post_types', function( $post_types ) {
	return array_merge( $post_types, array(
		'lesson-plan',
		'wporg_workshop',
	) );
} );

