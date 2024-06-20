<?php
/**
 * Set up query modifications.
 */

namespace WordPressdotorg\Theme\Learn_2024\Query;

add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_archive_queries' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_level_query' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_search_query' );


/**
 * Modify the query by adding meta query for language if set.
 *
 * @param WP_Query $query The query object.
 */
function modify_archive_queries( $query ) {
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
}

/**
 * Modify the main query.
 * If the 'all' level filter is set in the query, remove it to return all posts.
 *
 * @param WP_Query $query The main query.
 * @return WP_Query
 */
function modify_level_query( $query ) {
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
 * Get a list of the searchable Learn post types.
 *
 * @return array The searchable post types.
 */
function get_searchable_post_types() {
	return array( 'course', 'lesson', 'quiz', 'meeting', 'page', 'post', 'wporg_workshop' );
}

/**
 * Modify the search query to filter to only Learn post types if no post type is set.
 *
 * @param WP_Query $query The search query.
 */
function modify_search_query( $query ) {
	if ( is_admin() || ! $query->is_search() ) {
		return;
	}

	if ( isset( $query->query_vars['post_type'] ) ) {
		return $query;
	}

	$query->set( 'post_type', get_searchable_post_types() );

	return $query;
}
