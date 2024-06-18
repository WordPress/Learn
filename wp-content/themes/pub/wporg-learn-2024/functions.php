<?php

namespace WordPressdotorg\Theme\Learn_2024;

use function WPOrg_Learn\Sensei\{get_my_courses_page_url};

// Block files
require_once __DIR__ . '/src/learning-pathway-cards/index.php';
require_once __DIR__ . '/src/learning-pathway-header/index.php';
require_once __DIR__ . '/src/search-results-context/index.php';
require_once __DIR__ . '/src/upcoming-online-workshops/index.php';
require_once __DIR__ . '/src/sensei-meta-list/index.php';
require_once __DIR__ . '/inc/block-config.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_filter( 'wporg_block_site_breadcrumbs', __NAMESPACE__ . '\set_site_breadcrumbs' );
add_filter( 'wporg_block_navigation_menus', __NAMESPACE__ . '\add_site_navigation_menus' );
add_filter( 'single_template_hierarchy', __NAMESPACE__ . '\modify_single_template' );
remove_filter( 'template_include', array( 'Sensei_Templates', 'template_loader' ), 10, 1 );
add_filter( 'sensei_register_post_type_lesson', function( $args ) {
	$args['has_archive'] = 'lessons';
	return $args;
} );
add_filter( 'sensei_register_post_type_course', function( $args ) {
	$args['has_archive'] = 'courses';
	return $args;
} );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_learning_pathways_query' );

/**
 * Modify the single template hierarchy to use customised copies of the Sensei Course Theme templates.
 *
 * @param array $templates Array of template files.
 * @return array
 */
function modify_single_template( $templates ) {
	if ( is_singular( 'lesson' ) ) {
		array_unshift( $templates, 'single-lesson.html' );
	} elseif ( is_singular( 'quiz' ) ) {
		array_unshift( $templates, 'single-quiz.html' );
	}

	return $templates;
}

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
	add_theme_support( 'responsive-embeds' );

	add_filter( 'mkaz_code_syntax_force_loading', '__return_true' );
	add_filter( 'mkaz_prism_css_path', __NAMESPACE__ . '\update_prism_css_path' );
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	// The parent style is registered as `wporg-parent-2021-style`, and will be loaded unless
	// explicitly unregistered. We can load any child-theme overrides by declaring the parent
	// stylesheet as a dependency.
	wp_enqueue_style(
		'wporg-learn-2024-style',
		get_stylesheet_directory_uri() . '/build/style/style-index.css',
		array( 'wporg-parent-2021-style', 'wporg-global-fonts' ),
		filemtime( get_stylesheet_directory() . '/build/style/style-index.css' )
	);

	// Preload the heading font(s).
	if ( is_callable( 'global_fonts_preload' ) ) {
		/* translators: Subsets can be any of cyrillic, cyrillic-ext, greek, greek-ext, vietnamese, latin, latin-ext. */
		$subsets = _x( 'Latin', 'Heading font subsets, comma separated', 'wporg-learn' );
		// All headings.
		global_fonts_preload( 'EB Garamond, Inter', $subsets );
	}
}

/**
 * Customize the syntax highlighter style.
 * See https://github.com/PrismJS/prism-themes.
 *
 * @param string $path Path to the file to override, relative to the theme.
 * @return string
 */
function update_prism_css_path( $path ) {
	return '/build/prism/style-index.css';
}

/**
 * Provide a list of local navigation menus.
 */
function add_site_navigation_menus( $menus ) {
	$menu = array(
		array(
			'label' => __( 'Courses', 'wporg-learn' ),
			'url'   => '/courses/',
		),
		array(
			'label' => __( 'Lessons', 'wporg-learn' ),
			'url'   => '/lessons/',
		),
		array(
			'label' => __( 'Online Workshops', 'wporg-learn' ),
			'url'   => '/online-workshops/',
		),
		array(
			'label' => __( 'My courses', 'wporg-learn' ),
			'url'   => get_my_courses_page_url(),
		),
	);

	$learning_pathways = get_terms(
		array(
			'taxonomy'   => 'learning-pathway',
			'hide_empty' => true,
		)
	);

	if ( empty( $learning_pathways ) || is_wp_error( $learning_pathways ) ) {
		$menus['learn'] = $menu;

		return $menus;
	}

	$learning_pathways_menu = array(
		'label'   => __( 'Learning Pathways', 'wporg-learn' ),
		'submenu' => array_map( function( $term ) {
			return array(
				'label' => $term->name,
				'url'   => get_term_link( $term ),
			);
		}, $learning_pathways ),
	);

	array_unshift( $menu, $learning_pathways_menu );

	$menus['learn'] = $menu;

	return $menus;
}

/**
 * Get the titles and descriptions for the learning pathway levels.
 *
 * @param string $learning_pathway The learning pathway name.
 * @return array The content for the learning pathway levels.
 */
function get_learning_pathway_level_content( $learning_pathway ) {
	$content = array(
		'developer' => array(
			'beginner' => array(
				'title' => '[TBD Beginner developer title]',
				'description' => '[TBD Beginner developer description]',
			),
			'intermediate' => array(
				'title' => '[TBD Intermediate developer title]',
				'description' => '[TBD Intermediate developer description]',
			),
			'advanced' => array(
				'title' => '[TBD Advanced developer title]',
				'description' => '[TBD Advanced developer description]',
			),
		),
		'designer' => array(
			'beginner' => array(
				'title' => 'Begin exploring WordPress',
				'description' => 'Discover the design potential of WordPress.',
			),
			'intermediate' => array(
				'title' => 'Customize your site',
				'description' => 'Personalize and own all the details of your WordPress site.',
			),
			'advanced' => array(
				'title' => 'Elevate your site to stunning levels',
				'description' => 'For advanced users that are familiar with code.',
			),
		),
		'user' => array(
			'beginner' => array(
				'title' => '[TBD Beginner user title]',
				'description' => '[TBD Beginner user description]',
			),
			'intermediate' => array(
				'title' => '[TBD Intermediate user title]',
				'description' => '[TBD Intermediate user description]',
			),
			'advanced' => array(
				'title' => '[TBD Advanced user title]',
				'description' => '[TBD Advanced user description]',
			),
		),
		'contributor' => array(
			'beginner' => array(
				'title' => '[TBD Beginner contributor title]',
				'description' => '[TBD Beginner contributor description]',
			),
			'intermediate' => array(
				'title' => '[TBD Intermediate contributor title]',
				'description' => '[TBD Intermediate contributor description]',
			),
			'advanced' => array(
				'title' => '[TBD Advanced contributor title]',
				'description' => '[TBD Advanced contributor description]',
			),
		),
	);

	return $content[ $learning_pathway ];
}

/**
 * Filters breadcrumb items for the site-breadcrumb block.
 *
 * @param array $breadcrumbs
 *
 * @return array
 */
function set_site_breadcrumbs( $breadcrumbs ) {
	if ( isset( $breadcrumbs[0] ) ) {
		// Change the title of the first breadcrumb to 'Home'.
		$breadcrumbs[0]['title'] = 'Home';
	}

	$post_type = get_post_type();

	if ( is_singular() && 'page' !== $post_type && 'post' !== $post_type ) {
		// CPT single page: Insert the archive breadcrumb into the second position.
		$post_type_object = get_post_type_object( $post_type );
		$archive_title = $post_type_object->labels->name;
		$archive_url = get_post_type_archive_link( $post_type );

		$archive_breadcrumb = array(
			'url' => $archive_url,
			'title' => $archive_title,
		);

		array_splice( $breadcrumbs, 1, 0, array( $archive_breadcrumb ) );

		// If it's a lesson single page, change the second breadcrumb to the course archive
		// and insert the lesson course breadcrumb into the third position.
		if ( is_singular( 'lesson' ) ) {
			$lesson_course_id = get_post_meta( get_the_ID(), '_lesson_course', true );

			if ( empty( $lesson_course_id ) ) {
				return $breadcrumbs;
			}

			$post_type_object = get_post_type_object( 'course' );
			$archive_title = $post_type_object->labels->name;
			$archive_url = get_post_type_archive_link( 'course' );

			$archive_breadcrumb = array(
				'url' => $archive_url,
				'title' => $archive_title,
			);

			$lesson_course_title = get_the_title( $lesson_course_id );
			$lesson_course_link = get_permalink( $lesson_course_id );
			$lesson_course_breadcrumb = array(
				'url' => $lesson_course_link,
				'title' => $lesson_course_title,
			);

			$breadcrumbs[1] = $archive_breadcrumb;
			array_splice( $breadcrumbs, 2, 0, array( $lesson_course_breadcrumb ) );
		}
	}

	return $breadcrumbs;
}

/**
 * Modify the main query.
 * If the 'all' level filter is set in the query, remove it to return all posts.
 *
 * @param WP_Query $query The main query.
 * @return WP_Query
 */
function modify_learning_pathways_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$level = $query->get( 'wporg_lesson_level' );

	if ( 'all' === $level ) {
		$query->set( 'wporg_lesson_level', '' );
	}

	return $query;
}
