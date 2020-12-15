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
	remove_action( 'sensei_before_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper' ) );
	remove_action( 'sensei_after_main_content', array( $woothemes_sensei->frontend, 'sensei_output_content_wrapper_end' ) );

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
	wp_enqueue_script(
		'wporg-navigation',
		get_template_directory_uri() . '/js/navigation.js',
		array(),
		filemtime( __DIR__ . '/js/navigation.js' ),
		true
	);

	if ( is_post_type_archive( 'wporg_workshop' ) ) {
		wp_enqueue_style( 'select2' );
		wp_enqueue_script(
			'wporg-filters',
			get_theme_file_uri() . '/js/filters.js',
			array( 'jquery', 'select2' ),
			filemtime( __DIR__ . '/js/filters.js' ),
			true
		);
	}

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
	$GLOBALS['pagetitle'] = wp_title( '&#124;', false, 'right' ) . __( 'Learn WordPress', 'wporg-learn' );
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
 * Get the values associated to the page/post
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
 * Returns the taxonomies associated to a lesson or workshop
 *
 * @param int $post_id Id of the post.
 *
 * @return array
 */
function wporg_learn_get_lesson_plan_taxonomy_data( $post_id ) {
	return array(
		array(
			'icon'  => 'clock',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'duration' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms_string( $post_id, 'duration' ),
		),
		array(
			'icon'  => 'admin-users',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'audience' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms_string( $post_id, 'audience' ),
		),
		array(
			'icon'  => 'dashboard',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'level' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms_string( $post_id, 'level' ),
		),
		array(
			'icon'  => 'welcome-learn-more',
			'label' => wporg_label_with_colon( get_taxonomy_labels( get_taxonomy( 'instruction_type' ) )->singular_name ),
			'value' => wporg_learn_get_taxonomy_terms_string( $post_id, 'instruction_type' ),
		),
	);
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

	$valid_post_types = array( 'lesson-plan', 'wporg_workshop' );

	if ( $query->is_main_query() && $query->is_post_type_archive( $valid_post_types ) ) {
		wporg_archive_maybe_apply_query_filters( $query );

		if ( $query->is_post_type_archive( 'wporg_workshop' ) && true !== $query->get( 'wporg_workshop_filters' ) ) {
			$featured = wporg_get_featured_workshops();

			if ( ! empty( $featured ) ) {
				$featured = reset( $featured );
				$query->set( 'post__not_in', array( $featured->ID ) );
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
				'ID' => 'ASC',
			)
		);
	}

	if ( $query->is_main_query() && $query->is_tax( 'wporg_workshop_series' ) ) {
		$query->set( 'order', 'asc' );
	}
}
add_action( 'pre_get_posts', 'wporg_archive_modify_query' );

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
			'series'   => FILTER_SANITIZE_NUMBER_INT,
			'topic'    => FILTER_SANITIZE_NUMBER_INT,
			'language' => FILTER_SANITIZE_STRING,
			'captions' => FILTER_SANITIZE_STRING,
			'search'   => FILTER_SANITIZE_STRING,
		),
		false
	);

	$meta_query = array();
	$tax_query = array();
	$is_filtered = false;

	if ( is_array( $filters ) ) {
		$filters = array_filter( $filters );

		foreach ( $filters as $filter_name => $filter_value ) {
			switch ( $filter_name ) {
				case 'series':
					if ( ! empty( $tax_query ) ) {
						$tax_query['relation'] = 'AND';
					}
					$tax_query[] = array(
						'taxonomy' => 'wporg_workshop_series',
						'terms'    => $filter_value,
					);
					break;
				case 'topic':
					if ( ! empty( $tax_query ) ) {
						$tax_query['relation'] = 'AND';
					}
					$tax_query[] = array(
						'taxonomy' => 'topic',
						'terms'    => $filter_value,
					);
					break;
				case 'language':
					if ( ! empty( $meta_query ) ) {
						$meta_query['relation'] = 'AND';
					}
					$meta_query[] = array(
						'key'   => 'video_language',
						'value' => $filter_value,
					);
					break;
				case 'captions':
					if ( ! empty( $meta_query ) ) {
						$meta_query['relation'] = 'AND';
					}
					$meta_query[] = array(
						'key'   => 'video_caption_language',
						'value' => $filter_value,
					);
					break;
				case 'search':
					$query->set( 's', $filter_value );
					$is_filtered = true;
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
		$query->set( 'wporg_workshop_filters', true );
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
	$args = wp_parse_args( $args, array(
		'post_type'   => $post_type,
		'post_status' => 'publish',
	) );

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
	$post = get_post( $post_id );
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
				$completed    = count( Sensei()->course->get_completed_lesson_ids( $post_id, get_current_user_id() ) );

				$args['meta'][] = array(
					'icon'  => ( $lesson_count === $completed ) ? 'awards' : 'edit-large',
					'label' => __( 'Completed:', 'wporg-learn' ),
					'value' => $completed,
				);
			}
			break;

		case 'lesson-plan':
			$args['meta'] = wporg_learn_get_lesson_plan_taxonomy_data( $post_id );
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
 * Get the bio of a user, either from profiles.wordpress.org or usermeta.
 *
 * This relies on the availability of the `bpmain_bp_xprofile_data` table, so for local environments
 * it falls back on values in `usermeta`.
 *
 * @param WP_User $user
 *
 * @return string
 */
function wporg_get_workshop_presenter_bio( WP_User $user ) {
	global $wpdb;

	$bio = '';

	if ( 'local' !== wp_get_environment_type() ) {
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

	if ( ! $bio ) {
		$bio = $user->description;
	}

	return $bio;
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
 * Get the series taxonomy term object for a workshop post.
 *
 * @param int|WP_Post|null $workshop
 *
 * @return WP_Term|bool
 */
function wporg_workshop_series_get_term( $workshop = null ) {
	$workshop = get_post( $workshop );

	if ( ! $workshop instanceof WP_Post ) {
		return false;
	}

	$terms = wp_get_post_terms( $workshop->ID, 'wporg_workshop_series' );

	if ( empty( $terms ) ) {
		return false;
	}

	return $terms[0];
}

/**
 * Given a workshop post in a series, get all the workshop posts in the series.
 *
 * @param int|WP_Post|null $workshop
 *
 * @return WP_Post[]
 */
function wporg_workshop_series_get_siblings( $workshop = null ) {
	$term = wporg_workshop_series_get_term( $workshop );

	if ( ! $term ) {
		return array();
	}

	$args = array(
		'post_type'      => 'wporg_workshop',
		'post_status'    => 'publish',
		'posts_per_page' => 999,
		'order'          => 'asc',
		'tax_query'      => array(
			array(
				'taxonomy' => 'wporg_workshop_series',
				'terms'    => $term->term_id,
			),
		),
	);

	return get_posts( $args );
}

/**
 * Given a workshop post in a series, get an adjacent workshop post in the series.
 *
 * @param string           $which    Which adjacent post to retrieve. 'previous' or 'next'.
 * @param int|WP_Post|null $workshop
 *
 * @return WP_Post|bool
 */
function wporg_workshop_series_get_adjacent( $which, $workshop = null ) {
	if ( ! $workshop instanceof WP_Post ) {
		$workshop = get_post( $workshop );
	}

	$siblings    = wporg_workshop_series_get_siblings( $workshop );
	$sibling_ids = wp_list_pluck( $siblings, 'ID' );
	$index       = array_search( $workshop->ID, $sibling_ids, true );

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
