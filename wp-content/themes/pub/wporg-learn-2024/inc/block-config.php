<?php
/**
 * Set up configuration for dynamic blocks.
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Config;

add_filter( 'wporg_query_filter_options_level', __NAMESPACE__ . '\get_course_level_options' );

/**
 * Get the list of levels for the course filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_course_level_options( $options ) {
	global $wp_query;
	// Get top 10 levels ordered by count, then sort them alphabetically.
	$levels = get_terms(
		array(
			'taxonomy' => 'level',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 10,
		)
	);
	usort(
		$levels,
		function ( $a, $b ) {
			return strcmp( strtolower( $a->name ), strtolower( $b->name ) );
		}
	);
	$selected = isset( $wp_query->query['wporg_lesson_level'] ) ? (array) $wp_query->query['wporg_lesson_level'] : array();
	$count = count( $selected );
	$label = sprintf(
		/* translators: The dropdown label for filtering, %s is the selected term count. */
		_n( 'Experience levels <span>%s</span>', 'Experience levels <span>%s</span>', $count, 'wporg-learn' ),
		$count
	);
	return array(
		'label' => $label,
		'title' => __( 'Experience levels', 'wporg-learn' ),
		'key' => 'wporg_lesson_level',
		'action' => home_url( '/courses/' ),
		'options' => array_combine( wp_list_pluck( $levels, 'slug' ), wp_list_pluck( $levels, 'name' ) ),
		'selected' => $selected,
	);
}

