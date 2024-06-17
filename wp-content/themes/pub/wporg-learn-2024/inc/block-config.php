<?php
/**
 * Set up configuration for dynamic blocks.
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Config;

use function WPOrg_Learn\Post_Meta\{get_available_post_type_locales};

add_filter( 'wporg_query_filter_options_language', __NAMESPACE__ . '\get_language_options' );
add_filter( 'wporg_query_filter_options_level', __NAMESPACE__ . '\get_level_options' );
add_filter( 'wporg_query_filter_options_taxonomy-level', __NAMESPACE__ . '\get_taxonomy_level_options' );
add_filter( 'wporg_query_filter_options_learning-pathway-level', __NAMESPACE__ . '\get_learning_pathway_level_options' );
add_filter( 'wporg_query_filter_options_topic', __NAMESPACE__ . '\get_topic_options' );
add_filter( 'wporg_query_filter_options_taxonomy-topic', __NAMESPACE__ . '\get_taxonomy_topic_options' );
add_filter( 'wporg_query_filter_options_learning-pathway-topic', __NAMESPACE__ . '\get_learning_pathway_topic_options' );
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
 * Create level options.
 *
 * @param array $levels The filtered levels for a view.
 * @return array The options for a level filter.
 */
function create_level_options( $levels ) {
	global $wp_query;

	// If there are no levels, don't show the filter.
	if ( empty( $levels ) ) {
		return array();
	}

	// Sort the levels alphabetically.
	usort(
		$levels,
		function ( $a, $b ) {
			return strcmp( strtolower( $a->name ), strtolower( $b->name ) );
		}
	);

	// Add an 'All' option to the top.
	$levels = array_merge(
		array(
			'all' => (object) array(
				'slug' => 'all',
				'name' => __( 'All', 'wporg-learn' ),
			),
		),
		$levels,
	);

	$label = __( 'Level', 'wporg-learn' );

	$selected_slug = $wp_query->get( 'wporg_lesson_level' );
	if ( $selected_slug ) {
		// Find the selected level from $levels by slug and then get the name.
		$selected_level = array_filter(
			$levels,
			function ( $level ) use ( $selected_slug ) {
				return $level->slug === $selected_slug;
			}
		);
		if ( ! empty( $selected_level ) ) {
			$selected_level = array_shift( $selected_level );
			$label = $selected_level->name;
		}
	} else {
		$selected_slug = 'all';
		$label = __( 'All', 'wporg-learn' );
	}

	return array(
		'label' => $label,
		'title' => __( 'Level', 'wporg-learn' ),
		'key' => 'wporg_lesson_level',
		'action' => get_current_url(),
		'options' => array_combine( wp_list_pluck( $levels, 'slug' ), wp_list_pluck( $levels, 'name' ) ),
		'selected' => array( $selected_slug ),
	);
}

/**
 * Get the list of levels for the course and lesson filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_level_options( $options ) {
	global $wp_query;

	if ( ! isset( $wp_query->query_vars['post_type'] ) ) {
		return array();
	}

	// Get top 10 levels ordered by count, not empty, filtered by post_type.
	$object_ids = get_posts(
		array(
			'post_type' => $wp_query->query_vars['post_type'],
			'fields' => 'ids',
			'posts_per_page' => -1,
			'post_status' => 'publish',
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

	return create_level_options( $levels );
}

/**
 * Get the list of levels for the taxonomy filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_taxonomy_level_options( $options ) {
	// Get top 10 levels ordered by count, not empty.
	$levels = get_terms(
		array(
			'taxonomy' => 'level',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 10,
			'hide_empty' => true,
		)
	);

	return create_level_options( $levels );
}

/**
 * Get the list of levels for the learning pathway filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_learning_pathway_level_options( $options ) {
	global $wp_query;

	if ( ! isset( $wp_query->query_vars['wporg_learning_pathway'] ) ) {
		return array();
	}

	// Get top 10 levels ordered by count, not empty, filtered by post_type.
	$object_ids = get_posts(
		array(
			'fields' => 'ids',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_type' => 'course',
			'tax_query' => array(
				array(
					'taxonomy' => 'learning-pathway',
					'field' => 'slug',
					'terms' => $wp_query->query_vars['wporg_learning_pathway'],
				),
			),
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

	return create_level_options( $levels );
}

/**
 * Create topic options.
 *
 * @param array $topics The filtered topics for a view.
 * @return array The options for a topic filter.
 */
function create_topic_options( $topics ) {
	global $wp_query;

	// If there are no topics, or less than 2, don't show the filter.
	if ( empty( $topics ) || count( $topics ) < 2 ) {
		return array();
	}

	// Sort the topics alphabetically.
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
		'title' => __( 'Filter', 'wporg-learn' ),
		'key' => 'wporg_workshop_topic',
		'action' => get_current_url(),
		'options' => array_combine( wp_list_pluck( $topics, 'slug' ), wp_list_pluck( $topics, 'name' ) ),
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

	if ( ! isset( $wp_query->query_vars['post_type'] ) ) {
		return array();
	}

	// Get top 20 topics ordered by count, not empty, filtered by post_type.
	$object_ids = get_posts( array(
		'fields' => 'ids',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'post_type' => $wp_query->query_vars['post_type'],
	) );
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

	return create_topic_options( $topics );
}

/**
 * Get the list of topics for the taxonomy filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_taxonomy_topic_options( $options ) {
	// Get top 20 topics ordered by count, not empty.
	$topics = get_terms(
		array(
			'taxonomy' => 'topic',
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 20,
			'hide_empty' => true,
		)
	);

	return create_topic_options( $topics );
}

/**
 * Get the list of topics for the learning pathway filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_learning_pathway_topic_options( $options ) {
	global $wp_query;

	if ( ! isset( $wp_query->query_vars['wporg_learning_pathway'] ) ) {
		return array();
	}

	// Get top 20 topics ordered by count, not empty, filtered by post_type.
	$object_ids = get_posts(
		array(
			'fields' => 'ids',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_type' => 'course',
			'tax_query' => array(
				array(
					'taxonomy' => 'learning-pathway',
					'field' => 'slug',
					'terms' => $wp_query->query_vars['wporg_learning_pathway'],
				),
			),
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

	return create_topic_options( $topics );
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
		'title' => __( 'Filter', 'wporg-learn' ),
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

	$single_query_vars = array( 'wporg_lesson_level' );
	foreach ( $single_query_vars as $single_query_var ) {
		if ( ! isset( $wp_query->query[ $single_query_var ] ) ) {
			continue;
		}
		if ( $key === $single_query_var ) {
			continue;
		}
		$value = $wp_query->query[ $single_query_var ];
		printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $single_query_var ), esc_attr( $value ) );
	}

	$multi_query_vars = array( 'wporg_workshop_topic' );
	foreach ( $multi_query_vars as $multi_query_var ) {
		if ( ! isset( $wp_query->query[ $multi_query_var ] ) ) {
			continue;
		}
		if ( $key === $multi_query_var ) {
			continue;
		}
		$values = (array) $wp_query->query[ $multi_query_var ];
		foreach ( $values as $value ) {
			printf( '<input type="hidden" name="%s[]" value="%s" />', esc_attr( $multi_query_var ), esc_attr( $value ) );
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
