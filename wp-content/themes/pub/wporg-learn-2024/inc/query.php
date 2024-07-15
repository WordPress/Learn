<?php
/**
 * Set up query modifications.
 */

namespace WordPressdotorg\Theme\Learn_2024\Query;

add_action( 'pre_get_posts', __NAMESPACE__ . '\add_language_to_archive_queries' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\handle_all_level_query' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\add_excluded_to_lesson_archive_query' );

/**
 * Modify the query by adding meta query for language if set.
 *
 * @param WP_Query $query The query object.
 */
function add_language_to_archive_queries( $query ) {
	// Ensure this code runs only for the main query on archive pages and search results.
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_archive() || $query->is_search() ) ) {
		if ( isset( $_GET['language'] ) && is_array( $_GET['language'] ) ) {
			$languages = array_map( 'sanitize_text_field', $_GET['language'] );

			$meta_query = array( 'relation' => 'OR' );

			$meta_query[] = array(
				'key'     => 'language',
				'value'   => $languages,
				'compare' => 'IN',
			);

			// If 'en_US' is included, include posts with no language defined
			// as this is the default value for the meta field.
			if ( in_array( 'en_US', $languages ) ) {
				$meta_query[] = array(
					'key'     => 'language',
					'compare' => 'NOT EXISTS',
				);
			}

			$query->set( 'meta_query', $meta_query );
		}
	}

	return $query;
}

/**
 * Modify the main query.
 * If the 'all' level filter is set in the query, remove it to return all posts.
 *
 * @param WP_Query $query The main query.
 * @return WP_Query
 */
function handle_all_level_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$level = $query->get( 'wporg_lesson_level' );

	if ( 'all' === $level ) {
		$query->set( 'wporg_lesson_level', '' );
	}

	return $query;
}

/**
 * Modify the query by adding meta query for excluding the lesson from the archive if set.
 *
 * @param WP_Query $query The query object.
 */
function add_excluded_to_lesson_archive_query( $query ) {
	// Ensure this code runs only for the main query on lesson archive pages and search results.
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_archive( 'lesson' ) || $query->is_search() ) ) {
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key'     => '_lesson_archive_excluded',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_lesson_archive_excluded',
					'value'   => 'excluded',
					'compare' => '!=',
				),
			)
		);
	}

	return $query;
}
