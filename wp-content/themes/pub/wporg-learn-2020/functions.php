<?php
/**
 * WPBBP functions and definitions
 *
 * @package WPBBP
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'sensei' );

	global $woothemes_sensei;
	if ( $woothemes_sensei ) {
		remove_action( 'sensei_before_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper' ) );
		remove_action( 'sensei_after_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper_end' ) );
	}

	// The parent wporg theme is designed for use on wordpress.org/* and assumes locale-domains are available.
	// Remove hreflang support.
	remove_action( 'wp_head', 'WordPressdotorg\Theme\hreflang_link_attributes' );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );

/**
 * Enqueue the CSS styles & scripts.
 *
 * The wporg theme does this with a static version, so we have to have it here too with our own cache-busting version.
 * The version is set to the last modified time during development.
 */
function wporg_learn_scripts() {
	wp_enqueue_style(
		'wporg-style',
		get_theme_file_uri( '/css/style.css' ),
		array( 'dashicons', 'open-sans' ),
		filemtime( __DIR__ . '/css/style.css' )
	);
	if ( is_post_type_archive( array( 'wporg_workshop', 'lesson-plan' ) ) || is_singular( array( 'wporg_workshop', 'lesson-plan' ) ) ) {
		wp_enqueue_style(
			'wporg-print-style',
			get_theme_file_uri( '/css/print.css' ),
			array(),
			filemtime( __DIR__ . '/css/print.css' ),
			'print'
		);
	}
	wp_enqueue_script(
		'wporg-navigation',
		get_theme_file_uri() . '/js/navigation.js',
		array(),
		filemtime( __DIR__ . '/js/navigation.js' ),
		true
	);

	// Temporarily disabling the enhanced dropdowns for workshop filtering, see https://github.com/WordPress/Learn/issues/810

	// phpcs:ignore
	/* if ( is_post_type_archive( 'wporg_workshop' ) ) {
		wp_enqueue_style( 'select2' );
		wp_enqueue_script(
			'wporg-filters',
			get_theme_file_uri() . '/js/filters.js',
			array( 'jquery', 'select2' ),
			filemtime( __DIR__ . '/js/filters.js' ),
			true
		);
	}
	*/

	if ( is_post_type_archive( 'course' ) || is_search() ) {
		wp_dequeue_style( 'sensei-frontend' );
	}

	if ( is_front_page() ) {
		wp_enqueue_script( 'wporg-learn-event' );
	}
}
add_action( 'wp_enqueue_scripts', 'wporg_learn_scripts' );

/**
 * The Header for our theme.
 *
 * @package WPBBP
 */
function wporg_get_global_header() {
	require WPORGPATH . 'header.php';
}

/**
 * Get the taxonomies associated to workshop
 *
 * @package WPBBP
 */
function wporg_get_tax_slugs_from_workshop() {
	return wp_get_post_terms( get_the_ID(), 'lesson_group', array( 'fields' => 'slugs' ) );
}

/**
 * Get the lesson plans associated to a taxonomy
 *
 * @param string $slugs Comma separated list of taxonomy terms.
 * @package WPBBP
 */
function wporg_get_lesson_plans_by_tax_slugs_query( $slugs ) {
	$args = array(
		'post_type' => 'lesson-plan',
		'tax_query' => array(
			array(
				'taxonomy' => 'lesson_group',
				'field'    => 'slug',
				'terms'    => $slugs,
			),
		),
	);

	// Get all the lesson plans associated to
	return new WP_Query( $args );
}

/**
 * Get the category from the query vars
 *
 * @package WPBBP
 */
function wporg_get_filter_category() {
	return get_query_var( 'category' );
}

/**
 * Returns a list of filter categories
 *
 * @return array
 */
function wporg_get_filter_categories() {
	return get_categories();
}

/**
 * Returns the default filter category key
 *
 * @return string|null
 */
function wporg_get_default_cat() {
	$cats = wporg_get_filter_categories();

	return reset( $cats );
}

/**
 * Returns the default category if category is not defined
 *
 * @return string
 */
function wporg_get_cat_or_default_slug() {
	$cat = wporg_get_filter_category();

	if ( empty( $cat ) ) {
		return wporg_get_default_cat()->slug;
	}

	return $cat;
}

/**
 * Get the values associated to the page/post formatted as a string
 *
 * @param string $post_id Id of the post.
 * @param string $tax_slug The slug for the custom taxonomy.
 *
 * @return string
 */
function wporg_learn_get_taxonomy_terms_string( $post_id, $tax_slug ) {
	$terms = wp_get_post_terms( $post_id, $tax_slug, array( 'fields' => 'names' ) );

	return implode( ', ', $terms );
}

/**
 * Get the values associated to the page/post formatted as an array
 *
 * @param string $post_id Id of the post.
 * @param string $tax_slug The slug for the custom taxonomy.
 *
 * @return array
 */
function wporg_learn_get_taxonomy_terms_array( $post_id, $tax_slug ) {
	$term_ids = wp_get_post_terms( $post_id, $tax_slug, array( 'fields' => 'ids' ) );

	$terms = array();
	foreach ( $term_ids as $id ) {
		$terms[ $id ] = get_term( $id )->name;
	}

	return $terms;
}

/**
 * Get the values associated to the page/post according to the context
 *
 * @param  int    $post_id  ID of the post.
 * @param  string $tax_slug The slug for the custom taxonomy.
 * @param  string $context  The context for display.
 *
 * @return array|string
 */
function wporg_learn_get_taxonomy_terms( $post_id, $tax_slug, $context ) {
	switch ( $context ) {
		case 'archive':
			return wporg_learn_get_taxonomy_terms_string( $post_id, $tax_slug );
			break;
		case 'single':
			return wporg_learn_get_taxonomy_terms_array( $post_id, $tax_slug );
			break;
	}
}

/**
 * Returns the taxonomies associated to a lesson or workshop
 *
 * @param int $post_id Id of the post.
 *
 * @return array
 */
function wporg_learn_get_lesson_plan_taxonomy_data( $post_id, $context ) {
	$data = array(
		array(
			'icon'  => 'clock',
			'slug'  => 'duration',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'duration' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms( $post_id, 'duration', $context ),
		),
		array(
			'icon'  => 'admin-users',
			'slug'  => 'audience',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'audience' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms( $post_id, 'audience', $context ),
		),
		array(
			'icon'  => 'dashboard',
			'slug'  => 'level',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'level' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms( $post_id, 'level', $context ),
		),
		array(
			'icon'  => 'welcome-learn-more',
			'slug'  => 'type',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'instruction_type' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms( $post_id, 'instruction_type', $context ),
		),
	);

	$versions = wporg_learn_get_taxonomy_terms( $post_id, 'wporg_wp_version', $context );
	if ( $versions ) {
		$data[] = array(
			'icon'  => 'wordpress',
			'slug'  => 'wp_version',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'wporg_wp_version' ) )->singular_name ),
			'value' => $versions,
		);
	}

	return $data;
}

/**
 * Returns whether the post type is a workshop
 *
 * @return bool
 */
function wporg_post_type_is_workshop() {
	return get_post_type() == 'workshop';
}

/**
 * Modify the excerpt length for our custom post types.
 *
 * @param int $length Excerpt length.
 *
 * @return int (Maybe) modified excerpt length.
 */
function wporg_modify_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	if ( 'wporg_workshop' === get_post_type() ) {
		return 35;
	}

	return 25;
}
add_filter( 'excerpt_length', 'wporg_modify_excerpt_length', 999 );

/**
 * Change the query for workshops in some circumstances.
 *
 * @param WP_Query $query
 *
 * @return void
 */
function wporg_archive_modify_query( WP_Query $query ) {
	if ( is_admin() ) {
		return;
	}

	$valid_post_types = array( 'lesson-plan', 'wporg_workshop', 'course' );

	if ( $query->is_main_query() && $query->is_post_type_archive( $valid_post_types ) ) {
		wporg_archive_maybe_apply_query_filters( $query );

		if ( $query->is_post_type_archive( 'wporg_workshop' ) && true !== $query->get( 'wporg_archive_filters' ) ) {
			$featured = wporg_get_featured_workshops();

			if ( ! empty( $featured ) ) {
				$featured = reset( $featured );
				if ( ! $query->is_feed() ) {
					$query->set( 'post__not_in', array( $featured->ID ) );
				}
			}
		}
	}

	// Some lesson plans were created at exactly the same second, so we're adding the ID to the implicit sort order to avoid randomization.
	if (
		( $query->is_post_type_archive( 'lesson-plan' ) || $query->is_tax( 'wporg_lesson_category' ) ) &&
		empty( $query->get( 'orderby' ) )
	) {
		$query->set(
			'orderby',
			array(
				'post_date' => 'DESC',
				'ID'        => 'ASC',
			)
		);
	}

	if ( $query->is_main_query() && $query->is_tax( 'wporg_workshop_series' ) ) {
		$query->set( 'order', 'asc' );
	}

	// Possibly temporary until more of the courses are filled out.
	if ( $query->is_main_query() && $query->is_post_type_archive( 'course' ) ) {
		$query->set(
			'orderby',
			array(
				'post_date' => 'ASC',
				'ID'        => 'DESC',
			)
		);

		$query->set(
			'meta_query',
			array(
				array(
					'key'   => '_course_featured',
					'value' => 'featured',
				),
			)
		);

		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy'  => 'course-category',
					'field'     => 'id',
					'terms'     => get_terms( 'course-category', array( 'fields' => 'ids' ) ),
				),
			)
		);

		// Since courses are displayed in category groups, we don't need pagination
		$query->set( 'posts_per_page', '-1' );
	}

	// Omit some post types from search results.
	if ( $query->is_main_query() && $query->is_search() ) {
		$public_post_types = array_keys( get_post_types( array( 'public' => true ) ) );
		$omit_from_search = array( 'attachment', 'page', 'lesson', 'quiz', 'sensei_message', 'meeting' );
		$searchable_post_types = array_diff( $public_post_types, $omit_from_search );

		// Only show featured courses, but don't limit other post types
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key'   => '_course_featured',
					'value' => 'featured',
				),
				array(
					'key'      => '_course_featured',
					'compare'  => 'NOT EXISTS',
				),
			)
		);

		$query->set( 'post_type', $searchable_post_types );
	}
}
add_action( 'pre_get_posts', 'wporg_archive_modify_query' );

/**
 * Add ordering to query for advanced filtering
 *
 * @param  string $orderby
 * @param  object $query
 *
 * @return string
 */
function wporg_archive_orderby( $orderby, $query ) {
	global $wpdb;

	if ( is_admin() ) {
		return $orderby;
	}

	// Group courses by their category
	if ( $query->is_main_query() && $query->is_post_type_archive( 'course' ) ) {
		$orderby = $wpdb->term_relationships . '.term_taxonomy_id DESC, ' . $orderby;
	}

	return $orderby;
}
add_filter( 'posts_orderby', 'wporg_archive_orderby', 10, 2 );

/**
 * Modify the workshop post type archive query to prioritize workshops in the user's locale.
 *
 * In order to show all workshops, but with the ones that are presented/captioned in the user's locale shown first, we
 * need to modify the posts query in ways that can't be done through the WP_Query or WP_Meta_Query APIs. Instead, here,
 * we're filtering the individual clauses of the query to add the pieces we need.
 *
 * Examples, slightly truncated for simplicity:
 *
 * Before:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.ID
 * FROM wp_posts
 * WHERE 1=1
 * AND wp_posts.post_type = 'wporg_workshop'
 * ORDER BY wp_posts.post_date DESC
 *
 * After:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.*,
 *   MAX( IF( pmeta.meta_key = 'video_language' AND pmeta.meta_value LIKE 'art_%', 1, 0 ) ) AS has_language,
 *   MAX( IF( pmeta.meta_key = 'video_caption_language' AND pmeta.meta_value LIKE 'art_%', 1, 0 ) ) AS has_caption
 * FROM wp_posts
 * INNER JOIN wp_postmeta pmeta ON ( wp_posts.ID = pmeta.post_id )
 * WHERE 1=1
 * AND wp_posts.post_type = 'wporg_workshop'
 * GROUP BY wp_posts.ID
 * ORDER BY has_language DESC, has_caption DESC, wp_posts.post_date DESC
 *
 * @param array    $clauses
 * @param WP_Query $query
 *
 * @return array
 */
function wporg_archive_query_prioritize_locale( $clauses, $query ) {
	if ( ! $query->is_post_type_archive( 'wporg_workshop' ) || is_admin() ) {
		return $clauses;
	}

	global $wpdb;

	$locale      = get_locale();
	$locale_root = preg_replace( '#^([a-z]{2,3}_?)[a-zA-Z_-]*#', '$1', $locale, -1, $count );

	if ( $count ) {
		/**
		 * $clauses['fields'] contains the SELECT part of the query.
		 *
		 * The extra fields clauses are calculated fields that will contain a `1` if the workshop post row has a postmeta
		 * value that matches the locale root. The MAX() and the groupby clause below ensure that all the rows for a
		 * given workshop are consolidated into one with the highest value in the calculated column. Without the
		 * grouping, there would be a separate row for each postmeta value for each workshop post.
		 */
		$clauses['fields'] .= ",
			MAX( IF( pmeta.meta_key = 'video_language' AND pmeta.meta_value LIKE '{$locale_root}%', 1, 0 ) ) AS has_language
		";
		$clauses['fields'] .= ",
			MAX( IF( pmeta.meta_key = 'video_caption_language' AND pmeta.meta_value LIKE '{$locale_root}%', 1, 0 ) ) AS has_caption
		";
		$clauses['join']   .= " INNER JOIN {$wpdb->postmeta} pmeta ON ( {$wpdb->posts}.ID = pmeta.post_id )";
		// This orderby clause ensures that the workshops are sorted by the values in the calculated columns first.
		$clauses['orderby'] = 'has_language DESC, has_caption DESC, ' . $clauses['orderby'];

		if ( false === strpos( $clauses['groupby'], "{$wpdb->posts}.ID" ) ) {
			$clauses['groupby'] = "{$wpdb->posts}.ID";
		}
	}

	return $clauses;
}
add_filter( 'posts_clauses', 'wporg_archive_query_prioritize_locale', 10, 2 );

/**
 * Update a query object if filter parameters are present.
 *
 * @param WP_Query $query Query object, passed by reference.
 *
 * @return void
 */
function wporg_archive_maybe_apply_query_filters( WP_Query &$query ) {
	$filters = filter_input_array(
		INPUT_GET,
		array(
			'search'     => FILTER_SANITIZE_STRING,
			'captions'   => FILTER_SANITIZE_STRING,
			'language'   => FILTER_SANITIZE_STRING,
			'audience'   => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'duration'   => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'level'      => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'series'     => FILTER_VALIDATE_INT,
			'topic'      => FILTER_VALIDATE_INT,
			'type'       => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'wp_version' => array(
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_FORCE_ARRAY,
			),
		),
		false
	);

	$entity_map = array(
		'captions'   => 'video_caption_language',
		'language'   => 'video_language',
		'audience'   => 'audience',
		'duration'   => 'duration',
		'level'      => 'level',
		'topic'      => 'topic',
		'type'       => 'instruction_type',
		'wp_version' => 'wporg_wp_version',
	);

	$series_slug = wporg_learn_get_series_taxonomy_slug( $query->get( 'post_type' ) );
	if ( $series_slug ) {
		$entity_map['series'] = $series_slug;
	}

	$meta_query = array();
	$tax_query = array();

	$is_filtered = false;

	if ( is_array( $filters ) ) {
		$filters = array_filter( $filters );
		// Strip out `wp_version` if it's empty (converted to `array( false )`, due to FILTER_FORCE_ARRAY).
		if ( isset( $filters['wp_version'] ) && 0 === count( array_filter( $filters['wp_version'] ) ) ) {
			unset( $filters['wp_version'] );
		}

		// If both language and captions filters are set, we assume an "OR" relationship.
		if ( isset( $filters['captions'], $filters['language'] ) ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'   => $entity_map['captions'],
					'value' => $filters['captions'],
				),
				array(
					'key'   => $entity_map['language'],
					'value' => $filters['language'],
				),
			);

			unset( $filters['captions'], $filters['language'] );
		}

		foreach ( $filters as $filter_name => $filter_value ) {
			switch ( $filter_name ) {
				case 'search':
					$query->set( 's', $filter_value );
					$is_filtered = true;
					break;
				case 'captions':
				case 'language':
					if ( ! empty( $meta_query ) ) {
						$meta_query['relation'] = 'AND';
					}
					$meta_query[] = array(
						'key'   => $entity_map[ $filter_name ],
						'value' => $filter_value,
					);
					break;
				case 'audience':
				case 'duration':
				case 'level':
				case 'series':
				case 'topic':
				case 'type':
				case 'wp_version':
					if ( ! empty( $tax_query ) ) {
						$tax_query['relation'] = 'AND';
					}
					$tax_query[] = array(
						'taxonomy' => $entity_map[ $filter_name ],
						'terms'    => $filter_value,
					);
					break;
			}
		}
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
		$is_filtered = true;
	}

	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
		$is_filtered = true;
	}

	if ( $is_filtered ) {
		$query->set( 'wporg_archive_filters', true );
	}
}

/**
 * Get a query object for displaying workshop posts.
 *
 * @param string $post_type The post type of the archive.
 * @param array  $args      Arguments for the query.
 *
 * @return WP_Query
 */
function wporg_get_archive_query( $post_type, array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'   => $post_type,
			'post_status' => 'publish',
		)
	);

	return new WP_Query( $args );
}

/**
 * Get an array of data to be given to the card component template via the third argument of get_template_part().
 *
 * @param int $post_id
 *
 * @return array[]
 */
function wporg_learn_get_card_template_args( $post_id ) {
	$post      = get_post( $post_id );
	$post_type = get_post_type( $post );

	$args = array(
		'class' => array(),
		'meta'  => array(),
	);

	switch ( $post_type ) {
		case 'course':
			$lesson_count = Sensei()->course->course_lesson_count( $post_id );

			$args['meta'] = array(
				array(
					'icon'  => 'editor-ul',
					'label' => wporg_label_with_colon( get_post_type_labels( get_post_type_object( 'lesson' ) )->name ),
					'value' => $lesson_count,
				),
			);

			if ( is_user_logged_in() ) {
				$completed = count( Sensei()->course->get_completed_lesson_ids( $post_id, get_current_user_id() ) );

				$args['meta'][] = array(
					'icon'  => ( $lesson_count === $completed ) ? 'awards' : 'edit-large',
					'label' => __( 'Completed:', 'wporg-learn' ),
					'value' => $completed,
				);
			}
			break;

		case 'lesson-plan':
			$args['meta'] = wporg_learn_get_lesson_plan_taxonomy_data( $post_id, 'archive' );
			break;

		case 'wporg_workshop':
			$args['meta'] = array(
				array(
					'icon'  => 'category',
					'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'topic' ) )->singular_name ),
					'value' => wporg_learn_get_taxonomy_terms_string( $post_id, 'topic' ),
				),
				array(
					'icon'  => 'clock',
					'label' => __( 'Duration:', 'wporg-learn' ),
					'value' => \WPOrg_Learn\Post_Meta\get_workshop_duration( $post, 'string' ),
				),
				array(
					'icon'  => 'admin-site-alt3',
					'label' => __( 'Language:', 'wporg-learn' ),
					'value' => \WordPressdotorg\Locales\get_locale_name_from_code( $post->video_language, 'native' ),
				),
			);
			break;
	}

	return $args;
}

/**
 * Append a colon to a label string.
 *
 * Example: This is a self-referential example.
 *
 * @param string $label
 *
 * @return string
 */
function wporg_label_with_colon( $label ) {
	return sprintf(
		// translators: %s is a field label. This adds a colon, which will be followed by the contents of the field.
		__( '%s:', 'wporg-learn' ),
		$label
	);
}

/**
 * Get a number of workshop posts that are marked as "featured".
 *
 * Currently there is no taxonomy or postmeta value to mark a workshop as "featured",
 * so we're just grabbing the most recent workshops. This may change.
 *
 * @param int $number
 *
 * @return WP_Post[]
 */
function wporg_get_featured_workshops( $number = 1 ) {
	$query = wporg_get_archive_query(
		'wporg_workshop',
		array(
			'posts_per_page' => $number,
		)
	);

	return $query->get_posts();
}

/**
 * Returns the presenters for the workshop.
 *
 * @param WP_Post|int $workshop
 *
 * @return WP_User[]|array
 */
function wporg_get_workshop_presenters( $workshop = null ) {
	$post       = get_post( $workshop );
	$presenters = get_post_meta( $post->ID, 'presenter_wporg_username' );
	$wp_users   = array();

	foreach ( $presenters as $presenter ) {
		$wp_user = get_user_by( 'login', $presenter );

		if ( $wp_user ) {
			array_push( $wp_users, $wp_user );
		}
	}

	return $wp_users;
}

/**
 * Returns the other contributors for the workshop.
 *
 * @param WP_Post|int $workshop
 *
 * @return WP_User[]|array
 */
function wporg_get_workshop_other_contributors( $workshop = null ) {
	$post               = get_post( $workshop );
	$other_contributors = get_post_meta( $post->ID, 'other_contributor_wporg_username' );
	$wp_users           = array();

	foreach ( $other_contributors as $other_contributor ) {
		$wp_user = get_user_by( 'login', $other_contributor );

		if ( $wp_user ) {
			array_push( $wp_users, $wp_user );
		}
	}

	return $wp_users;
}

/**
 * Get the bio of a user, first trying usermeta and then profiles.wordpress.org.
 *
 * The `usermeta` bio (description) field will be pulled. If there is no bio, profiles.wordpress.org is tried.
 * The bio at profiles.wordpress.org relies on the availability of the `bpmain_bp_xprofile_data` table.
 * For local environments the bio will only pull from `usermeta`.
 *
 * @param WP_User $user The user to retrieve a bio for.
 *
 * @return string
 */
function wporg_get_workshop_presenter_bio( WP_User $user ) {
	global $wpdb;

	// Retrieve bio from user data.
	$bio = $user->description;

	// If bio is empty, retrieve from .org.
	if ( ! $bio && 'local' !== wp_get_environment_type() ) {
		$xprofile_field_id = 3;

		$sql = $wpdb->prepare(
			'
				SELECT value
				FROM bpmain_bp_xprofile_data
				WHERE user_id = %1$d
				AND field_id = %2$d
			',
			$user->ID,
			$xprofile_field_id
		);

		$bio = $wpdb->get_var( $sql ) ?: ''; // phpcs:ignore WordPress.DB.PreparedSQL -- prepare called above.
	}

	return apply_filters( 'the_content', wp_unslash( $bio ) );
}

/**
 * Display a featured image, falling back to the VideoPress thumbnail if no featured image was explicitly set.
 *
 * @param WP_Post $post The Workshop post for which we want the thumbnail.
 * @param string  $size The image size: 'medium', 'full'.
 */
function wporg_get_post_thumbnail( $post, $size = 'post-thumbnail' ) {
	$thumbnail = get_the_post_thumbnail( $post, $size );
	if ( $thumbnail ) {
		return $thumbnail;
	} else {
		$post = get_post( $post );
		foreach ( get_post_meta( $post->ID, '', true ) as $key => $value ) {
			if ( substr( $key, 0, 8 ) === '_oembed_' && preg_match( '#https://video.wordpress.com/embed/(\w+)#', $value[0], $match ) ) {
				$video = videopress_get_video_details( $match[1] );
				if ( ! is_wp_error( $video ) && isset( $video->poster ) ) {
					return '<img class="attachment-' . esc_attr( $size ) . ' wp-post-image" src=' . esc_url( $video->poster ) . ' loading="lazy" alt="" />';
				}
			}
		}
	}
}

/**
 * Conditionally change or remove the prefix from archive titles.
 *
 * @param string $prefix
 *
 * @return string
 */
function wporg_modify_archive_title_prefix( $prefix ) {
	if ( is_post_type_archive() ) {
		return '';
	}

	return sprintf(
		'<span class="archive-title-prefix">%s</span>',
		$prefix
	);
}
add_filter( 'get_the_archive_title_prefix', 'wporg_modify_archive_title_prefix' );

/**
 * Append pagination to the archive title.
 *
 * @global WP_Query $wp_query
 * @global int $paged
 *
 * @param string $title
 *
 * @return mixed
 */
function wporg_modify_archive_title( $title ) {
	global $wp_query, $paged;

	if ( $paged > 1 ) {
		$suffix = sprintf(
			__( 'Page %1$d of %2$d', 'wporg-learn' ),
			absint( $paged ),
			absint( $wp_query->max_num_pages )
		);

		$title = sprintf(
			// translators: 1: Archive title; 2: Pagination, e.g. Page 2 of 4.
			__( '%1$s &ndash; %2$s', 'wporg-learn' ),
			$title,
			$suffix
		);
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'wporg_modify_archive_title' );

/**
 * Get the slug for the series taxonomy for a given post type.
 *
 * @param string $post_type
 *
 * @return false|string
 */
function wporg_learn_get_series_taxonomy_slug( $post_type ) {
	$tax_slug = false;

	switch ( $post_type ) {
		case 'lesson-plan':
			$tax_slug = 'wporg_lesson_plan_series';
			break;
		case 'wporg_workshop':
			$tax_slug = 'wporg_workshop_series';
			break;
	}

	return $tax_slug;
}

/**
 * Get the series taxonomy term object for a post.
 *
 * @param int|WP_Post|null $post
 *
 * @return WP_Term|bool
 */
function wporg_learn_series_get_term( $post = null ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$tax_slug = wporg_learn_get_series_taxonomy_slug( get_post_type( $post ) );
	$terms = wp_get_post_terms( $post->ID, $tax_slug );

	if ( empty( $terms ) ) {
		return false;
	}

	return $terms[0];
}

/**
 * Given a post in a series, get all the posts in the series.
 *
 * @param int|WP_Post|null $post
 *
 * @return WP_Post[]
 */
function wporg_learn_series_get_siblings( $post = null ) {
	$post_type = get_post_type( $post );
	$term = wporg_learn_series_get_term( $post );

	if ( ! $term ) {
		return array();
	}

	$args = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => 999,
		'order'          => 'asc',
		'tax_query'      => array(
			array(
				'taxonomy' => wporg_learn_get_series_taxonomy_slug( $post_type ),
				'terms'    => $term->term_id,
			),
		),
	);

	return get_posts( $args );
}

/**
 * Given a post in a series, get an adjacent post in that series.
 *
 * @param string           $which    Which adjacent post to retrieve. 'previous' or 'next'.
 * @param int|WP_Post|null $post
 *
 * @return WP_Post|bool
 */
function wporg_learn_series_get_adjacent( $which, $post = null ) {
	if ( ! $post instanceof WP_Post ) {
		$post = get_post( $post );
	}

	$siblings    = wporg_learn_series_get_siblings( $post );
	$sibling_ids = wp_list_pluck( $siblings, 'ID' );
	$index       = array_search( $post->ID, $sibling_ids, true );

	if ( false === $index ) {
		return false;
	}

	switch ( $which ) {
		case 'previous':
			$index --;
			break;
		case 'next':
			$index ++;
			break;
	}

	return $siblings[ $index ] ?? false;
}

/**
 * Robots "noindex" rules for specific parts of the Learn site.
 *
 * @param bool $noindex
 *
 * @return bool
 */
function wporg_learn_noindex( $noindex ) {
	if ( is_singular( 'quiz' ) ) {
		$noindex = true;
	}

	return $noindex;
}
add_filter( 'wporg_noindex_request', 'wporg_learn_noindex' );

/**
 * Fixes bug in (or at least in using) SyntaxHighlighter code shortcodes that
 * causes double-encoding of `>` character.
 *
 * Copied from themes/pub/wporg-developer/inc/formatting.php
 *
 * @param string $content The text being handled as code.
 * @return string
 */
function wporg_learn_fix_code_entity_encoding( $content ) {
	return str_replace( '&amp;gt;', '&gt;', $content );
}
add_filter( 'syntaxhighlighter_htmlresult', 'wporg_learn_fix_code_entity_encoding', 20 );

/**
 * Register theme sidebars.
 */
function wporg_learn_register_sidebars() {
	// Register lesson plans sidebar.
	register_sidebar(
		array(
			'name'          => __( 'Lesson Plans', 'wporg-learn' ),
			'id'            => 'wporg-learn-lesson-plans',
			'before_widget' => '<div id="%1$s" class="block-widgets %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '<h4>',
		)
	);

	// Register courses sidebar.
	register_sidebar(
		array(
			'name'          => __( 'Courses', 'wporg-learn' ),
			'id'            => 'wporg-learn-courses',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	// Register workshops sidebar.
	register_sidebar(
		array(
			'name'          => __( 'Workshops', 'wporg-learn' ),
			'id'            => 'wporg-learn-workshops',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);
}
add_filter( 'widgets_init', 'wporg_learn_register_sidebars', 10 );

/**
 * Add fallback image to Jetpack when no featured image exists.
 *
 * @param string $default_image The default image URL.
 *
 * @return string Image URL.
 */
function wporg_learn_return_default_image( $default_image ) {
	return 'https://s.w.org/images/learn-thumbnail-fallback.jpg';
}
add_action( 'jetpack_open_graph_image_default', 'wporg_learn_return_default_image', 15, 1 );

/**
 * Disable the News XML Sitemap generated by Jetpack
 */
add_filter( 'jetpack_news_sitemap_generate', '__return_false' );

/**
 * Redirect meeting posts to associated link
 *
 * @return void
 */
function wporg_learn_redirect_meetings() {
	global $post;

	if ( is_singular( array( 'meeting' ) ) ) {

		if ( ! empty( $post->ID ) ) {

			$redirect = wp_http_validate_url( get_post_meta( $post->ID, 'link', true ) );

			if ( $redirect && wp_redirect( $redirect ) ) {
				exit;
			}
		}
	}

}
add_action( 'template_redirect', 'wporg_learn_redirect_meetings' );

/**
 * Redirect old pages to their new homes.
 *
 * @return void
 */
function wporg_learn_redirect_old_urls() {
	if ( ! is_404() ) {
		return;
	}

	$redirects = array(
		// Source => Destination, any characters after the source will be appended to the destination.
		'/workshop/'                      => '/tutorial/',
		'/workshops'                      => '/tutorials',
		'/social-learning'                => '/online-workshops',
		'/workshop-presenter-application' => '/tutorial-presenter-application',
	);

	// Use `REQUEST_URI` rather than `$wp->request`, to get the entire source URI including url parameters.
	$request = $_SERVER['REQUEST_URI'] ?? '';

	foreach ( $redirects as $source => $destination ) {
		if ( str_starts_with( $request, $source ) ) {
			$redirect = $destination;

			// Append any extra request parameters.
			if ( strlen( $request ) > strlen( $source ) ) {
				$redirect .= substr( $request, strlen( $source ) );
			}

			wp_safe_redirect( $redirect );
			die();
		}
	}
}
add_action( 'template_redirect', 'wporg_learn_redirect_old_urls' );

/**
 * Add file MIME types for upload.
 *
 * @param  array $mime_types Default array of MIME types.
 *
 *  @return array Updated MIME type array.
 */
function wporg_learn_mime_types( $mime_types ) {
	$mime_types['vtt'] = 'text/vtt';
	return $mime_types;
}
add_filter( 'mime_types', 'wporg_learn_mime_types' );
