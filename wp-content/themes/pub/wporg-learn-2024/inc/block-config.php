<?php
/**
 * Set up configuration for dynamic blocks.
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Config;

add_filter( 'wporg_query_filter_options_level', __NAMESPACE__ . '\get_course_level_options' );
add_filter( 'wporg_query_filter_options_topic', __NAMESPACE__ . '\get_course_topic_options' );
add_action( 'wporg_query_filter_in_form', __NAMESPACE__ . '\inject_other_filters' );

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

/**
 * Get the list of topics for the course filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_course_topic_options( $options ) {
	global $wp_query;
	// Get top 20 topics ordered by count, then sort them alphabetically.
	$topics = get_terms(
		array(
			'taxonomy' => 'topic',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 20,
		)
	);
	usort(
		$topics,
		function ( $a, $b ) {
			return strcmp( strtolower( $a->name ), strtolower( $b->name ) );
		}
	);
	$selected = isset( $wp_query->query['wporg_workshop_topic'] ) ? (array) $wp_query->query['wporg_workshop_topic'] : array();
	$count = count( $selected );
	$label = sprintf(
		/* translators: The dropdown label for filtering, %s is the selected term count. */
		_n( 'Topics <span>%s</span>', 'Topics <span>%s</span>', $count, 'wporg-learn' ),
		$count
	);
	return array(
		'label' => $label,
		'title' => __( 'Topics', 'wporg-learn' ),
		'key' => 'wporg_workshop_topic',
		'action' => home_url( '/courses/' ),
		'options' => array_combine( wp_list_pluck( $topics, 'slug' ), wp_list_pluck( $topics, 'name' ) ),
		'selected' => $selected,
	);
}

/**
 * Add in the other existing filters as hidden inputs in the filter form.
 *
 * Enables combining filters by building up the correct URL on submit,
 * for example courses using a topic and a level:
 *   ?wporg_workshop_topic[]=extending-wordpress&wporg_lesson_level[]=beginner`
 *
 * @param string $key The key for the current filter.
 */
function inject_other_filters( $key ) {
	global $wp_query;

	$query_vars = array( 'wporg_workshop_topic', 'wporg_lesson_level' );
	foreach ( $query_vars as $query_var ) {
		if ( ! isset( $wp_query->query[ $query_var ] ) ) {
			continue;
		}
		if ( $key === $query_var ) {
			continue;
		}
		$values = (array) $wp_query->query[ $query_var ];
		foreach ( $values as $value ) {
			printf( '<input type="hidden" name="%s[]" value="%s" />', esc_attr( $query_var ), esc_attr( $value ) );
		}
	}
}
