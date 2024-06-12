<?php
/**
 * Set up configuration for dynamic blocks.
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Config;

use function WPOrg_Learn\Post_Meta\{get_available_post_type_locales};

add_filter( 'wporg_query_filter_options_language', __NAMESPACE__ . '\get_language_options' );
add_filter( 'wporg_query_filter_options_level', __NAMESPACE__ . '\get_level_options' );
add_filter( 'wporg_query_filter_options_topic', __NAMESPACE__ . '\get_topic_options' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_query' );
add_action( 'wporg_query_filter_in_form', __NAMESPACE__ . '\inject_other_filters' );

/**
 * Get the current URL.
 *
 * @return string The current URL.
 */
function get_current_url() {
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Get the list of levels for the course and lesson filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_level_options( $options ) {
	global $wp_query;
	$post_type = $wp_query->query_vars['post_type'];
	// Get top 10 levels ordered by count, not empty, filtered by post_type, then sort them alphabetically.
	$object_ids = get_posts(
		array(
			'post_type' => $post_type,
			'fields' => 'ids',
			'numberposts' => -1,
			'status' => 'publish',
		)
	);
	$levels = get_terms(
		array(
			'taxonomy' => 'level',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 10,
			'hide_empty' => true,
			'object_ids' => $object_ids,
		)
	);
	// If there are no levels, or less than 2, don't show the filter.
	if ( empty( $levels ) || count( $levels ) < 2 ) {
		return array();
	}

	usort(
		$levels,
		function ( $a, $b ) {
			return strcmp( strtolower( $a->name ), strtolower( $b->name ) );
		}
	);
	// Move the level with value 'Any' to the top, if it exists.
	$any_level = array_filter(
		$levels,
		function ( $level ) {
			return 'Any' === $level->name;
		}
	);
	if ( ! empty( $any_level ) ) {
		$target_key = key( $any_level );
		$target_element = array( $target_key => $levels[ $target_key ] );

		unset( $levels[ $target_key ] );

		$levels = $target_element + $levels;
	}

	$selected = isset( $wp_query->query['wporg_lesson_level'] ) ? (array) $wp_query->query['wporg_lesson_level'] : array();
	$count = count( $selected );
	$label = sprintf(
		/* translators: The dropdown label for filtering, %s is the selected term count. */
		_n( 'Level <span>%s</span>', 'Level <span>%s</span>', $count, 'wporg-learn' ),
		$count
	);

	return array(
		'label' => $label,
		'title' => __( 'Level', 'wporg-learn' ),
		'key' => 'wporg_lesson_level',
		'action' => get_current_url(),
		'options' => array_combine( wp_list_pluck( $levels, 'slug' ), wp_list_pluck( $levels, 'name' ) ),
		'selected' => $selected,
	);
}

/**
 * Get the list of topics for the course and lesson filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_topic_options( $options ) {
	global $wp_query;
	$post_type = $wp_query->query_vars['post_type'];
	// Get top 20 topics ordered by count, not empty, filtered by post_type, then sort them alphabetically.
	$object_ids = get_posts(
		array(
			'post_type' => $post_type,
			'fields' => 'ids',
			'numberposts' => -1,
			'status' => 'publish',
		)
	);
	$topics = get_terms(
		array(
			'taxonomy' => 'topic',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 20,
			'hide_empty' => true,
			'object_ids' => $object_ids,
		)
	);
	// If there are no topics, or less than 2, don't show the filter.
	if ( empty( $topics ) || count( $topics ) < 2 ) {
		return array();
	}

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
		_n( 'Topic <span>%s</span>', 'Topic <span>%s</span>', $count, 'wporg-learn' ),
		$count
	);

	return array(
		'label' => $label,
		'title' => __( 'Topic', 'wporg-learn' ),
		'key' => 'wporg_workshop_topic',
		'action' => get_current_url(),
		'options' => array_combine( wp_list_pluck( $topics, 'slug' ), wp_list_pluck( $topics, 'name' ) ),
		'selected' => $selected,
	);
}


/**
 * Get the meta query values by key.
 *
 * @param WP_Query $query The query.
 * @param string   $key The meta key.
 * @return array   The meta query values.
 */
function get_meta_query_values_by_key( $query, $key ) {
	if ( isset( $query->query_vars['meta_query'] ) ) {
		$meta_query = $query->query_vars['meta_query'];

		foreach ( $meta_query as $meta ) {
			if ( isset( $meta['key'] ) && $meta['key'] === $key && ! empty( $meta['value'] ) ) {
				return $meta['value'];
			}
		}
	}

	return array();
}

/**
 * Get the list of languages for the course and lesson filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of language options.
 */
function get_language_options( $options ) {
	global $wp_query;
	$post_type = $wp_query->query_vars['post_type'];
	$languages = get_available_post_type_locales( 'language', $post_type, 'publish' );
	// If there are no languages, or the only language is en_US, don't show the filter.
	if ( empty( $languages ) || ( 1 === count( $languages ) && isset( $languages['en_US'] ) ) ) {
		return array();
	}
	// Otherwise if there are other languages and en_US is not listed, add it to the top,
	// as this is the default value for the meta field.
	if ( ! isset( $languages['en_US'] ) ) {
		$languages = array_merge( array( 'en_US' => 'English' ), $languages );
	}

	$selected = get_meta_query_values_by_key( $wp_query, 'language' );
	$count = count( $selected );
	$label = sprintf(
		/* translators: The dropdown label for filtering, %s is the selected term count. */
		_n( 'Language <span>%s</span>', 'Language <span>%s</span>', $count, 'wporg-learn' ),
		$count
	);

	return array(
		'label' => $label,
		'title' => __( 'Language', 'wporg-learn' ),
		'key' => 'language',
		'action' => get_current_url(),
		'options' => $languages,
		'selected' => $selected,
	);
}

/**
 * Modify the query by adding meta query for language if set.
 *
 * @param WP_Query $query The query object.
 */
function modify_query( $query ) {
	// Ensure this code runs only for the main query on archive pages
	if ( ! is_admin() && $query->is_main_query() && $query->is_archive() ) {
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

	$meta_query_vars = array( 'language' );
	foreach ( $meta_query_vars as $meta_query_var ) {
		$values = (array) get_meta_query_values_by_key( $wp_query, $meta_query_var );
		if ( empty( $values ) ) {
			continue;
		}
		if ( $key === $meta_query_var ) {
			continue;
		}
		foreach ( $values as $value ) {
			printf( '<input type="hidden" name="%s[]" value="%s" />', esc_attr( $meta_query_var ), esc_attr( $value ) );
		}
	}
}
