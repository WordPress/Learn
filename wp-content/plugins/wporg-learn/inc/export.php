<?php

/**
 * Allow some raw data to be exposed in the REST API for certain post types, so that developers can import
 * a copy of production data for local testing.
 *
 * ⚠️ Be careful to only expose public data!
 */

namespace WPOrg_Learn\Export;

defined( 'WPINC' ) || die();

add_action( 'rest_api_init', function() {
	// Important: only expose raw post content for specific post types
	register_raw_content_for_post_type( 'lesson-plan' );
	register_raw_content_for_post_type( 'wporg_workshop' );
} );

/**
 * Register a `wporg_export` context for a given post type.
 *
 * @param string $post_type Post type to allow for export.
 */
function register_raw_content_for_post_type( $post_type ) {

	register_rest_field(
		$post_type,
		'content_raw',
		array(
			'get_callback' => __NAMESPACE__ . '\show_post_content_raw',
			'schema'       => array(
				'type' => 'string',
				'context' => array( 'wporg_export' ),
			),
		)
	);

	add_filter( "rest_{$post_type}_item_schema", __NAMESPACE__ . '\add_export_context_to_schema' );
}

/**
 * Filter a CPT item schema and make it so that every item with 'view' context also has 'export' context.
 *
 * @param array $schema The schema object.
 */
function add_export_context_to_schema( $schema ) {
	update_schema_array_recursive( $schema );

	return $schema;
}

/**
 * Find every item in the schema that has a 'view' context, and add an 'export' context to it.
 * Had to use a recursive function because array_walk_recursive only walks leaf nodes.
 *
 * @param array $schema The schema object.
 */
function update_schema_array_recursive( &$schema ) {
	foreach ( $schema as $key => &$value ) {
		// Head recursion
		if ( is_array( $value ) ) {
			update_schema_array_recursive( $value );
		}
		if ( 'context' === $key && in_array( 'view', $value ) ) {
			$value[] = 'wporg_export';
		}
	}
}

/**
 * Given an array of blocks, return an array of just the names of those blocks.
 *
 * @param array $blocks An array of blocks.
 * @return array An array of block names.
 */
function get_all_block_names( $blocks ) {
	$block_names = array();
	if ( ! $blocks ) {
		return array();
	}
	foreach ( $blocks as $block ) {
		if ( null !== $block['blockName'] ) {
			$block_names[] = $block['blockName'];
			if ( $block['innerBlocks'] ) {
				// Recursive call to get inner blocks
				$block_names = array_merge( $block_names, get_all_block_names( $block['innerBlocks'] ) );
			}
		}
	}

	return array_unique( $block_names );
}

/**
 * Callback: If a post contains only allowed blocks, then return the raw block markup for the post.
 *
 * @param array  $object The post object relating to the REST request.
 * @param string $field_name The field name.
 * @param array  $request The request object.
 *
 * @return string The raw post content, if it contains only allowed blocks; a placeholder string otherwise.
 */
function show_post_content_raw( $object, $field_name, $request ) {

	/**
	 * Filter: Modify the list of blocks permitted in posts available via the 'export' context.
	 * Posts containing any other blocks will not be exported.
	 *
	 * @param array $allowed_blocks An array of allowed block names. Simple wildcards are permitted, like 'core/*'.
	 */
	$allowed_blocks = apply_filters( 'allow_raw_block_export', array(
		'core/*',
		'wporg/*',
		// other allowed blocks:
		'jetpack/image-compare',
		'jetpack/tiled-gallery',
		'syntaxhighlighter/code',
	) );

	if ( ! empty( $object['id'] ) ) {
		$post = get_post( $object['id'] );
	} else {
		$post = get_post();
	}

	// Exit early if the post contains any blocks that are not explicitly allowed.
	if ( $post && has_blocks( $post->post_content ) || true ) {

		$regexes = array();
		foreach ( $allowed_blocks as $allowed_block_name ) {
			$regexes[] = strtr( preg_quote( $allowed_block_name, '#' ), array( '\*' => '.*' ) );
		}

		$regex = '#^(' . implode( '|', $regexes ) . ')$#';

		$blocks = parse_blocks( $post->post_content );
		$block_names = get_all_block_names( $blocks );

		foreach ( $block_names as $block_name ) {
			// If it contains a disallowed block, then return no content.
			// Better to raise an error instead?
			if ( ! preg_match( $regex, $block_name ) ) {
				return '<p>Post contains a disallowed block ' . esc_html( $block_name ) . '</p>';
			}
		}
	}

	return $post->post_content;
}
