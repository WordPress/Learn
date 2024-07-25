<?php
/**
 * Adds aria labels to the blocks in the card course template.
 */

namespace WordPressdotorg\Theme\Learn_2024\Card_Course;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\init' );

/**
 * Add the scripts to update the blocks in the card course template.
 *
 * The dependencies are autogenerated in block.json, and can be read with
 * `wp_json_file_decode` & `register_block_script_handle.
 */
function init() {
	$metadata_file = dirname( dirname( __DIR__ ) ) . '/build/card-course/block.json';
	$metadata = wp_json_file_decode( $metadata_file, array( 'associative' => true ) );
	$metadata['file'] = $metadata_file;

	$script_handle = register_block_script_handle( $metadata, 'viewScript', 0 );

	// Enqueue the assets when the card course template is on the page.
	add_action(
		'render_block_core/template-part',
		function( $block_content, $block ) use ( $script_handle ) {
			$slugs = array( 'card-course-h3', 'card-course', 'card-lesson-h3', 'card-lesson', 'card' );
			if ( isset( $block['attrs']['slug'] ) && in_array( $block['attrs']['slug'], $slugs, true ) ) {
				wp_enqueue_script( $script_handle );
			}
			return $block_content;
		},
		10,
		2
	);

	// Enqueue the assets when the my courses page content pattern is on the page.
	// The blocks composition of this pattern is similar to that of the card course.
	add_action(
		'render_block_core/pattern',
		function( $block_content, $block ) use ( $script_handle ) {
			if ( isset( $block['attrs']['slug'] ) && 'wporg-learn-2024/page-my-courses-content' === $block['attrs']['slug'] ) {
				wp_enqueue_script( $script_handle );
			}
			return $block_content;
		},
		10,
		2
	);
}
