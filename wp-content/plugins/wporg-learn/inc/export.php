<?php

/**
 * Allow some raw data to be exposed in the REST API for certain post types, so that developers can import
 * a copy of production data for local testing.
 */

namespace WPOrg_Learn\Export;

defined( 'WPINC' ) || die();

add_action( 'rest_api_init', function() {
	// Important: only expose raw post content for specific post types
	register_raw_content_for_post_type( 'lesson-plan' );
	register_raw_content_for_post_type( 'wporg_workshop' );
} );



function register_raw_content_for_post_type( $post_type ) {

	register_rest_field(
		$post_type,
		'content_raw',
		[
			'get_callback' => 'show_post_content_raw',
			'schema'       => [
				'type' => 'string',
				'context' => [ 'export' ]
			]
		]
	);
}

function get_all_block_names( $blocks ) {
	$block_names = [];

	foreach ( $blocks as $block ) {
		$block_names[] = $block[ 'blockName' ];
		if ( $block[ 'innerBlocks' ] ) {
			// Recursive call to get inner blocks
			$block_names = array_merge( $block_names, get_all_block_names( $block[ 'innerBlocks' ] ) );
		}
	}

	return array_unique( $block_names );
}

function show_post_content_raw( $object, $field_name, $request ) {
	// Blocks that are allowed to be revealed via the export context.
	// Posts that contain any other blocks will not expose raw content.
	$allowed_blocks = [
		// core/* assumed safe by default; is this a reasonable assumption?
		// other allowed blocks:
		'wporg/callout',
		'jetpack/image-compare',
		'jetpack/tiled-gallery',
		'syntaxhighlighter/code',
	];

	$post = get_post( $object[ 'id' ] );
	if ( $post && has_blocks( $post->post_content ) ) {
		$blocks = parse_blocks( $post->post_content );
		$block_names = get_all_block_names( $block_names );


		foreach ( $block_names as $block_name ) {
			// Allow all core blocks
			if ( 'core/' === substr( $block_name, 0, 5 ) ) {
				continue;
			}
			// If it contains a disallowed block, then return no content.
			// Better to raise an error instead?
			if ( !in_array( $block_name, $allowed_blocks ) ) {
				return false;
			}
		}
	}

	return $post->post_content;
}