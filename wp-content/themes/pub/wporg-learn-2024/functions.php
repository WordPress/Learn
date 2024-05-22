<?php

namespace WordPressdotorg\Theme\Learn_2024;

// Block files
require_once __DIR__ . '/src/learning-pathway-cards/block.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
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
