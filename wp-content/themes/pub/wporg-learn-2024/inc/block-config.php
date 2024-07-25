<?php
/**
 * Set up configuration for dynamic blocks.
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Config;

use function WPOrg_Learn\Post_Meta\{get_available_post_type_locales};
use Sensei_Learner;

add_filter( 'wporg_query_filter_options_language', __NAMESPACE__ . '\get_language_options' );
add_filter( 'wporg_query_filter_options_archive_language', __NAMESPACE__ . '\get_language_options_by_post_type' );

add_filter( 'wporg_query_filter_options_level', __NAMESPACE__ . '\get_level_options' );
add_filter( 'wporg_query_filter_options_archive_level', __NAMESPACE__ . '\get_level_options_by_post_type' );
add_filter( 'wporg_query_filter_options_learning_pathway_level', __NAMESPACE__ . '\get_learning_pathway_level_options' );

add_filter( 'wporg_query_filter_options_topic', __NAMESPACE__ . '\get_topic_options' );
add_filter( 'wporg_query_filter_options_archive_topic', __NAMESPACE__ . '\get_topic_options_by_post_type' );
add_filter( 'wporg_query_filter_options_learning_pathway_topic', __NAMESPACE__ . '\get_learning_pathway_topic_options' );

add_filter( 'query_vars', __NAMESPACE__ . '\add_student_course_filter_query_vars' );
add_filter( 'wporg_query_filter_options_student_course', __NAMESPACE__ . '\get_student_course_options' );
add_action( 'wporg_query_filter_in_form', __NAMESPACE__ . '\inject_other_filters' );
add_filter( 'query_loop_block_query_vars', __NAMESPACE__ . '\modify_course_query' );

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
 * Create the options for a level filter.
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
 * Get the top 10 level options for a post type.
 * Used for the archive filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_level_options_by_post_type( $options ) {
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
 * Get the top 10 level options.
 * Used for the taxonomy and search filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of level options.
 */
function get_level_options( $options ) {
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
 * Get the top 10 level options for a learning pathway.
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
 * Create the options for a topic filter.
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
 * Get the top 20 topic options for a post type.
 * Used for the archive filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_topic_options_by_post_type( $options ) {
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
 * Get the top 20 topic options.
 * Used for the taxonomy and search filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of topic options.
 */
function get_topic_options( $options ) {
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
 * Get the top 20 topic options for a learning pathway.
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
 * Find the value in a multidimensional array by key.
 *
 * @param array  $array The array to search.
 * @param string $key The key to search for.
 * @return mixed|null The value if found, null otherwise.
 */
function find_value_by_key( $array, $key ) {
	if ( ! is_array( $array ) ) {
		return null;
	}

	if ( isset( $array['key'] ) && $key === $array['key'] && isset( $array['value'] ) ) {
		return $array['value'];
	}

	foreach ( $array as $element ) {
		$result = find_value_by_key( $element, $key );

		if ( null !== $result ) {
			return $result;
		}
	}

	return null;
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
		$values = find_value_by_key( $query->query_vars['meta_query'], $key );

		return is_array( $values ) ? $values : array();
	}

	return array();
}

/**
 * Create the options for a language filter.
 *
 * @param array $languages The filtered languages for a view.
 * @return array The options for a language filter.
 */
function create_language_options( $languages ) {
	global $wp_query;

	// If there are no languages, or the only language is en_US, or a search is set, don't show the filter.
	if ( empty( $languages ) || ( 1 === count( $languages ) && isset( $languages['en_US'] ) ) || $wp_query->get( 's' ) ) {
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
 * Get the full list of available languages that have content.
 * Used for the taxonomy filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of language options.
 */
function get_language_options( $options ) {
	$languages = get_available_post_type_locales( 'language', null, 'publish', 'native' );

	return create_language_options( $languages );
}

/**
 * Get the list of languages for a post_type.
 * Used for the archive filters.
 *
 * @param array $options The options for this filter.
 * @return array New list of language options.
 */
function get_language_options_by_post_type( $options ) {
	global $wp_query;
	$post_type = $wp_query->get( 'post_type' );
	// Convert post type from array to string if possible.
	if ( is_array( $post_type ) && count( $post_type ) === 1 ) {
		$post_type = reset( $post_type );
	}

	if ( ! is_string( $post_type ) ) {
		return array();
	}

	$languages = get_available_post_type_locales( 'language', $post_type, 'publish', 'native' );

	return create_language_options( $languages );
}

/**
 * Get the query variable name for the student course filter, used by Sensei to filter the My Courses page.
 * This is the PARAM_KEY defined in the Sensei plugin + the query id on the query loop in my-courses-content.php
 * See class Sensei_Course_List_Student_Course_Filter.
 *
 * @return string The query variable name.
 */
function get_student_course_filter_query_var_name() {
	return 'course-list-student-course-filter-0';
}

/**
 * Add student course filter query var.
 *
 * @param array $query_vars The query vars.
 * @return array The updated query vars.
 */
function add_student_course_filter_query_vars( $query_vars ) {
	$query_vars[] = get_student_course_filter_query_var_name();

	return $query_vars;
}

/**
 * Get the options for the student course completion status filter.
 *
 * @param array $options The options for this filter.
 * @return array New list of student course filter options.
 */
function get_student_course_options( $options ) {
	global $wp_query;

	$key = get_student_course_filter_query_var_name();

	$options = array(
		'all' => __( 'All', 'wporg-learn' ),
		'active' => __( 'Active', 'wporg-learn' ),
		'completed' => __( 'Completed', 'wporg-learn' ),
	);

	$selected_slug = $wp_query->get( $key );
	if ( $selected_slug ) {
		// Find the selected option from $options by slug and then get the name.
		$selected_option = array_filter(
			$options,
			function ( $option, $slug ) use ( $selected_slug ) {
				return $slug === $selected_slug;
			},
			ARRAY_FILTER_USE_BOTH
		);
		if ( ! empty( $selected_option ) ) {
			$label = array_shift( $selected_option );
		}
	} else {
		$selected_slug = 'all';
		$label = __( 'All', 'wporg-learn' );
	}

	return array(
		'label' => $label,
		'title' => __( 'Completion status', 'wporg-learn' ),
		'key' => $key,
		'action' => get_current_url(),
		'options' => $options,
		'selected' => array( $selected_slug ),
	);
}

/**
 * Add in the other existing filters as hidden inputs in the filter form.
 *
 * Enables combining filters by building up the correct URL on submit,
 * for example courses using a topic and a level:
 *   ?wporg_workshop_topic[]=extending-wordpress&wporg_lesson_level=beginner`
 *
 * @param string $key The key for the current filter.
 */
function inject_other_filters( $key ) {
	global $wp_query;

	$single_query_vars = array( 'wporg_lesson_level', 'wporg_learning_pathway', 'post_type' );
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

	// Pass through search query.
	if ( isset( $wp_query->query['s'] ) ) {
		printf( '<input type="hidden" name="s" value="%s" />', esc_attr( $wp_query->query['s'] ) );
	}
}

/**
 * Modify the course query on the 'My Courses' page to display courses according to the filter status.
 * Corresponds to https://github.com/Automattic/sensei/blob/trunk/includes/blocks/course-list/class-sensei-course-list-student-course-filter.php#L95
 *
 * @param array $query The course query.
 * @return array The modified course query.
 */
function modify_course_query( $query ) {
	if ( get_the_ID() === Sensei()->settings->get_my_courses_page_id() ) {
		$key             = get_student_course_filter_query_var_name();
		$selected_option = isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';

		// The courses query with 'active' and 'completed' statuses have already been filtered in Sensei LMS, and can correctly display the course lists.
		// See https://github.com/Automattic/sensei/blob/trunk/includes/blocks/course-list/class-sensei-course-list-student-course-filter.php#L114-L123.
		if ( ! empty( $selected_option ) && ( 'active' === $selected_option || 'completed' === $selected_option ) ) {
			return $query;
		}

		$learner_manager = Sensei_Learner::instance();
		$user_id         = get_current_user_id();
		$args            = array(
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		// The courses query with 'all' or any other statuses.
		$courses_query = $learner_manager->get_enrolled_courses_query( $user_id, $args );
		$course_ids    = $courses_query->posts;

		$query['post__in'] = $course_ids;
	}

	return $query;
}
