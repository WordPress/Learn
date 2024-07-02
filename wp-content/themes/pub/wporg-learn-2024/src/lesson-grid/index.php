<?php
namespace WordPressdotorg\Theme\Learn_2024\Lesson_Grid;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_lesson_grid_assets' );

add_filter( 'pre_render_block', __NAMESPACE__ . '\modify_lesson_query', 10, 2 );
add_filter( 'rest_lesson_query', __NAMESPACE__ . '\modify_lesson_rest_query', 10, 2 );

/**
 * Enqueue lesson grid assets.
 *
 * @throws Error If the build files are not found.
 */
function enqueue_lesson_grid_assets() {
	$script_asset_path = get_stylesheet_directory() . '/build/lesson-grid/index.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/lesson-grid" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_enqueue_script(
		'wporg-learn-lesson-grid',
		get_stylesheet_directory_uri() . '/build/lesson-grid/index.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);
}

/**
 * Modify the lesson query to add the featured lesson meta query if set.
 *
 * @param mixed $pre_render The pre-render value.
 * @param mixed $parsed_block The parsed block value.
 * @return mixed The modified lesson query.
 */
function modify_lesson_query( $pre_render, $parsed_block ) {
	if ( isset( $parsed_block['attrs']['namespace'] ) && 'wporg-learn/lesson-grid' === $parsed_block['attrs']['namespace']
	) {
		add_filter(
			'query_loop_block_query_vars',
			function( $query, $block ) use ( $parsed_block ) {
				if ( 'lesson' !== $query['post_type'] || ! isset( $parsed_block['attrs']['query']['lessonFeatured'] ) ) {
					return $query;
				}

				$lesson_featured = $parsed_block['attrs']['query']['lessonFeatured'];

				if ( true === $lesson_featured ) {
					$query['meta_key']   = '_lesson_featured';
					$query['meta_value'] = 'featured';
				}

				return $query;
			},
			10,
			2
		);
	}

	return $pre_render;
}

/**
 * Modify the lesson REST query to add the featured lesson meta query if set.
 *
 * @param array           $args The query arguments.
 * @param WP_REST_Request $request The REST request object.
 * @return array The modified query arguments.
 */
function modify_lesson_rest_query( $args, $request ) {
	$lesson_featured = $request->get_param( 'lessonFeatured' );

	if ( 'true' === $lesson_featured ) {
		$args['meta_query'][] = array(
			'key'     => '_lesson_featured',
			'value'   => 'featured',
			'compare' => '=',
		);
	}

	return $args;
}
