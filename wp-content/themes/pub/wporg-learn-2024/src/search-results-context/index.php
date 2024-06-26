<?php
/**
 * Block Name: Search Results Context
 * Description: Displays context information for search results.
 *
 * @package wporg-learn
 */

namespace WordPressdotorg\Theme\Learn_2024\Search_Results_Context;

add_action( 'init', __NAMESPACE__ . '\init' );

/**
 * Render the block content.
 *
 * @return string Returns the block markup.
 */
function render( $attributes ) {
	global $wp_query;

	if ( ! is_search() ) {
		return '';
	}

	$results_count = $wp_query->found_posts;

	if ( 0 === $results_count ) {
		return;
	}

	$posts_per_page = get_query_var( 'posts_per_page' );
	$current_page = get_query_var( 'paged' ) ?: 1;
	$first_result = ( $current_page - 1 ) * $posts_per_page + 1;
	$last_result = min( $current_page * $posts_per_page, $results_count );

	$content = sprintf(
		/* translators: %1$s number of results; %2$s keyword. */
		_n(
			'%1$s result found for "%2$s".',
			'%1$s results found for "%2$s".',
			$results_count,
			'wporg-learn'
		),
		number_format_i18n( $results_count ),
		esc_html( $wp_query->query['s'] ),
	);

	$filters = get_applied_filter_info( $wp_query );

	$showing = $results_count > 1
		? sprintf(
			/* translators: %1$s number of first displayed result, %2$s number of last displayed result. */
			'Showing results %1$s to %2$s.',
			number_format_i18n( $first_result ),
			number_format_i18n( $last_result ),
		)
		: '';

	$wrapper_attributes = get_block_wrapper_attributes();

	return sprintf(
		'<%1$s %2$s>%3$s %4$s %5$s</%1$s>',
		esc_attr( $attributes['tagName'] ),
		$wrapper_attributes,
		$content,
		$filters,
		$showing,
	);
}

/**
 * Get a description of the number of filters applied.
 *
 * @param WP_Query $query The WP_Query object.
 * @return string Returns the filter information.
 */
function get_applied_filter_info( $query ) {
	$filters_count = 0;

	// Add the level filter count
	if (
		isset( $query->query_vars['wporg_lesson_level'] )
		&& ! empty( $query->query_vars['wporg_lesson_level']
		&& 'all' !== $query->query_vars['wporg_lesson_level'] )
	) {
		// Level is a single value filter
		$filters_count++;
	}

	// Add the topic filter count
	if ( isset( $query->query_vars['wporg_workshop_topic'] ) && ! empty( $query->query_vars['wporg_workshop_topic'] ) ) {
		// Topic is a multiple value filter
		$filters_count += count( $query->query_vars['wporg_workshop_topic'] );
	}

	// Add the language filter count
	if ( isset( $query->query_vars['meta_query'] ) && is_array( $query->query_vars['meta_query'] ) ) {
		foreach ( $query->query_vars['meta_query'] as $meta_query ) {
			if (
				isset( $meta_query['key'] ) && 'language' === $meta_query['key']
				&& isset( $meta_query['value'] ) && is_array( $meta_query['value'] )
				) {
				// Language is a multiple value filter
				$filters_count += count( $meta_query['value'] );
			}
		}
	}

	return $filters_count > 0 ? sprintf(
		/* translators: %s number of filters. */
		_n(
			'%s filter applied.',
			'%s filters applied.',
			$filters_count,
			'wporg-learn'
		),
		number_format_i18n( $filters_count )
	) : '';
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function init() {
	register_block_type(
		dirname( dirname( __DIR__ ) ) . '/build/search-results-context',
		array(
			'render_callback' => __NAMESPACE__ . '\render',
		)
	);
}
