<?php
/**
 * Set up query modifications.
 */

namespace WordPressdotorg\Theme\Learn_2024\Query;

add_action( 'pre_get_posts', __NAMESPACE__ . '\add_language_to_archive_queries' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\filter_hidden_lessons_from_archive_and_search' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\filter_search_queries_by_post_type' );
add_filter( 'request', __NAMESPACE__ . '\handle_all_level_query' );
add_filter( 'jetpack_search_es_wp_query_args', __NAMESPACE__ . '\filter_jetpack_wp_search_query', 10, 2 );
add_filter( 'jetpack_search_es_query_args', __NAMESPACE__ . '\filter_jetpack_es_search_query', 10, 2 );

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
}

/**
 * Modify lessons archive and search queries by adding a taxonomy query for filtering out hidden lessons.
 *
 * @param WP_Query $query The query object.
 */
function filter_hidden_lessons_from_archive_and_search( $query ) {
	// Ensure this code runs only for the main query on lesson archive pages and search results.
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_archive( 'lesson' ) || $query->is_search() ) ) {
		$tax_query = $query->get( 'tax_query', array() );

		$exclude_lessons_by_taxonomy = array(
			'taxonomy' => 'show',
			'field'    => 'slug',
			'terms'    => 'hidden',
			'operator' => 'NOT IN',
		);

		// If there's an existing tax query, add the new condition
		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$tax_query[] = $exclude_lessons_by_taxonomy;
		} else {
			$tax_query = array( $exclude_lessons_by_taxonomy );
		}

		$query->set( 'tax_query', $tax_query );
	}
}

/**
 * Filter search queries by post type.
 * Only include courses and lessons in search results unless post_type is set, eg. for an archive search.
 *
 * @param WP_Query $query The query object.
 * @return WP_Query The modified query object.
 */
function filter_search_queries_by_post_type( $query ) {
	if ( ! is_admin() && $query->is_search() && $query->is_main_query() ) {
		if ( ! $query->get( 'post_type' ) ) {
			$query->set( 'post_type', array( 'course', 'lesson' ) );
		}
	}

	return $query;
}

/**
 * Modify the request.
 *
 * Update the query_vars to reset 'all' to an empty string.
 *
 * @param array $query_vars The array of requested query variables.
 *
 * @return array
 */
function handle_all_level_query( $query_vars ) {
	if ( is_admin() ) {
		return $query_vars;
	}

	$level = $query_vars['wporg_lesson_level'] ?? '';

	if ( 'all' === $level ) {
		$query_vars['wporg_lesson_level'] = '';
	}

	return $query_vars;
}

/**
 * Remove incorrectly applied "show" taxonomy search filter from Jetpack Search queries, if set.
 *
 * @see https://developer.jetpack.com/hooks/jetpack_search_es_wp_query_args/
 *
 * @param array     $query_args The current query args, in WP_Query format.
 * @param \WP_Query $query     The original query object.
 */
function filter_jetpack_wp_search_query( $query_args, $query ) {
	if ( isset( $query_args['terms']['show'] ) ) {
		unset( $query_args['terms']['show'] );
	}

	return $query_args;
}

/**
 * Modify the underlying ES query that is passed to the Jetpack Search endpoint to support NOT IN slug tax queries.
 *
 * @see https://developer.jetpack.com/hooks/jetpack_search_es_query_args/
 *
 * @param array     $es_query_args The current query args, in WP_Query format.
 * @param \WP_Query $query         The original query object.
 */
function filter_jetpack_es_search_query( $es_query_args, $query ) {
	$tax_query = $query->get( 'tax_query', array() );
	$must_not  = array();
	foreach ( $tax_query as $tax_query_item ) {
		if ( isset( $tax_query_item['operator'] ) && 'NOT IN' === $tax_query_item['operator'] && isset( $tax_query_item['field'] ) && 'slug' === $tax_query_item['field'] ) {
			$must_not[] = array( 'terms' => array( "taxonomy.{$tax_query_item['taxonomy']}.slug" => (array) $tax_query_item['terms'] ) );
		}
	}
	if ( empty( $must_not ) ) {
		return $es_query_args;
	}
	$es_query_args['query'] = array(
		'bool' => array(
			'must' => array( $es_query_args['query'] ),
			'must_not' => $must_not,
		),
	);

	return $es_query_args;
}
