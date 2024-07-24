<?php

namespace WordPressdotorg\Theme\Learn_2024;

use function WPOrg_Learn\Sensei\{get_my_courses_page_url, get_lesson_has_published_course};

// Block files
require_once __DIR__ . '/src/code/index.php';
require_once __DIR__ . '/src/course-grid/index.php';
require_once __DIR__ . '/src/course-outline/index.php';
require_once __DIR__ . '/src/learning-pathway-cards/index.php';
require_once __DIR__ . '/src/learning-pathway-header/index.php';
require_once __DIR__ . '/src/lesson-grid/index.php';
require_once __DIR__ . '/src/search-results-context/index.php';
require_once __DIR__ . '/src/sensei-progress-bar/index.php';
require_once __DIR__ . '/src/sidebar-meta-list/index.php';
require_once __DIR__ . '/src/upcoming-online-workshops/index.php';
require_once __DIR__ . '/inc/block-config.php';
require_once __DIR__ . '/inc/block-hooks.php';
require_once __DIR__ . '/inc/query.php';
require_once __DIR__ . '/inc/head.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\maybe_enqueue_sensei_assets', 100 );

add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\set_default_featured_image', 10, 5 );
add_filter( 'sensei_register_post_type_course', function( $args ) {
	$args['has_archive'] = 'courses';
	return $args;
} );
add_filter( 'sensei_register_post_type_lesson', function( $args ) {
	$args['has_archive'] = 'lessons';
	return $args;
} );
add_filter( 'single_template_hierarchy', __NAMESPACE__ . '\modify_single_template' );
add_filter( 'wporg_block_navigation_menus', __NAMESPACE__ . '\add_site_navigation_menus' );
add_filter( 'wporg_block_site_breadcrumbs', __NAMESPACE__ . '\set_site_breadcrumbs' );

remove_filter( 'template_include', array( 'Sensei_Templates', 'template_loader' ), 10, 1 );

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
	$style_path = get_stylesheet_directory() . '/build/style/style-index.css';
	$style_uri = get_stylesheet_directory_uri() . '/build/style/style-index.css';
	wp_enqueue_style(
		'wporg-learn-2024-style',
		$style_uri,
		array( 'wporg-parent-2021-style', 'wporg-global-fonts' ),
		filemtime( $style_path )
	);
	wp_style_add_data( 'wporg-learn-2024-style', 'path', $style_path );

	$rtl_file = str_replace( '.css', '-rtl.css', $style_path );
	if ( is_rtl() && file_exists( $rtl_file ) ) {
		wp_style_add_data( 'wporg-learn-2024-style', 'rtl', 'replace' );
		wp_style_add_data( 'wporg-learn-2024-style', 'path', $rtl_file );
	}

	// Preload the heading font(s).
	if ( is_callable( 'global_fonts_preload' ) ) {
		/* translators: Subsets can be any of cyrillic, cyrillic-ext, greek, greek-ext, vietnamese, latin, latin-ext. */
		$subsets = _x( 'Latin', 'Heading font subsets, comma separated', 'wporg-learn' );
		// All headings.
		global_fonts_preload( 'EB Garamond, Inter', $subsets );
	}
}

/**
 * Sensei doesn't enqueue learning mode styles for Lessons which are not part of a course.
 * Enqueue the styles and add the required body class if needed.
 */
function maybe_enqueue_sensei_assets() {
	if ( ( is_singular( 'lesson' ) || is_singular( 'quiz' ) ) && ! wp_style_is( 'sensei-course-theme-style', 'enqueued' ) ) {
		wp_enqueue_style( 'sensei-learning-mode' );

		add_filter( 'body_class', function( $classes ) {
			$sensei_body_class = 'sensei-course-theme';

			if ( ! in_array( $sensei_body_class, $classes, true ) ) {
				$classes[] = $sensei_body_class;
			}

			return $classes;
		} );
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
			'label'     => __( 'My courses', 'wporg-learn' ),
			'url'       => get_my_courses_page_url(),
			'className' => 'has-separator',
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
		'user' => array(
			'beginner' => array(
				'title' => __( 'Beginner WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re new to WordPress, or building websites, and want the essentials.', 'wporg-learn' ),
				'see_all_aria_label' => 'Beginner WordPress users: See all learning pathways',
			),
			'intermediate' => array(
				'title' => __( 'Intermediate WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re comfortable setting up your site and making small changes or you’ve already completed the Beginner course.', 'wporg-learn' ),
				'see_all_aria_label' => 'Intermediate WordPress users: See all learning pathways',
			),
			'advanced' => array(
				'title' => __( 'Advanced WordPress users', 'wporg-learn' ),
				'description' => __( 'You’re confident using multiple plugins and know how to customize a Block theme, or you’ve already completed the Intermediate course.', 'wporg-learn' ),
				'see_all_aria_label' => 'Advanced WordPress users: See all learning pathways',
			),
		),
		'developer' => array(
			'beginner' => array(
				'title' => __( 'Beginner development concepts', 'wporg-learn' ),
				'description' => __( 'You’re new to development or have experience using WordPress’s no-code features and want to do more.', 'wporg-learn' ),
				'see_all_aria_label' => 'Beginner development concepts: See all learning pathways',
			),
			'intermediate' => array(
				'title' => __( 'Intermediate development concepts', 'wporg-learn' ),
				'description' => __( 'You’re comfortable writing code and want to extend WordPress with your own plugin or theme.', 'wporg-learn' ),
				'see_all_aria_label' => 'Intermediate development concepts: See all learning pathways',
			),
			'advanced' => array(
				'title' => __( 'Advanced development concepts', 'wporg-learn' ),
				'description' => __( 'You’re confident in the WordPress development environment or have already built your own plugin or theme.', 'wporg-learn' ),
				'see_all_aria_label' => 'Advanced development concepts: See all learning pathways',
			),
		),
	);

	return $content[ $learning_pathway ];
}

/**
 * Filters breadcrumb items for the site-breadcrumb block.
 *
 * @param array $breadcrumbs The current breadcrumbs.
 *
 * @return array The modified breadcrumbs.
 */
function set_site_breadcrumbs( $breadcrumbs ) {
	if ( isset( $breadcrumbs[0] ) ) {
		// Change the title of the first breadcrumb to 'Home'.
		$breadcrumbs[0]['title'] = 'Home';
	}

	$post_id = get_the_ID();
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
			if ( ! get_lesson_has_published_course( $post_id ) ) {
				return $breadcrumbs;
			}

			$post_type_object = get_post_type_object( 'course' );
			$archive_title = $post_type_object->labels->name;
			$archive_url = get_post_type_archive_link( 'course' );

			$archive_breadcrumb = array(
				'url' => $archive_url,
				'title' => $archive_title,
			);

			$lesson_course_id = get_post_meta( $post_id, '_lesson_course', true );
			$lesson_course_title = get_the_title( $lesson_course_id );
			$lesson_course_link = get_permalink( $lesson_course_id );
			$lesson_course_breadcrumb = array(
				'url' => $lesson_course_link,
				'title' => $lesson_course_title,
			);

			$breadcrumbs[1] = $archive_breadcrumb;
			array_splice( $breadcrumbs, 2, 0, array( $lesson_course_breadcrumb ) );
		}
	} else {
		// Add the ancestors of the current page to the breadcrumbs.
		$ancestors = get_post_ancestors( $post_id );

		if ( ! empty( $ancestors ) ) {
			foreach ( $ancestors as $ancestor ) {
				$ancestor_post = get_post( $ancestor );

				$ancestor_breadcrumb = array(
					'url' => get_permalink( $ancestor_post ),
					'title' => get_the_title( $ancestor_post ),
				);

				array_splice( $breadcrumbs, 1, 0, array( $ancestor_breadcrumb ) );
			}
		}
	}

	// Ensure breadcrumbs are displayed only when there are at least 3 levels.
	$breadcrumb_level = count( $breadcrumbs );
	if ( $breadcrumb_level < 3 ) {
		$breadcrumbs = array();
	}

	return $breadcrumbs;
}

/**
 * Set the default featured image.
 *
 * @param string       $html The HTML for the featured image.
 * @param int          $post_id The post ID.
 * @param int          $post_thumbnail_id The post thumbnail ID.
 * @param string|array $size The image size.
 * @param string|array $attr The image attributes.
 * @return string The modified HTML for the featured image.
 */
function set_default_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! $html ) {
		return '<img src="https://s.w.org/images/learn-thumbnail-fallback.jpg?v=4" alt="' . esc_attr( get_the_title( $post_id ) ) . '" />';
	}

	return $html;
}
