<?php

namespace WordPressdotorg\Theme\Learn_2024;

/**
 * Shortcut to the build directory.
 *
 * @return string
 */
function get_build_path() {
	return get_stylesheet_directory() . '/build/';
}

/**
 * Shortcut to the build URL.
 *
 * @return string
 */
function get_build_url() {
	return get_stylesheet_directory_uri() . '/build/';
}

/**
 * Shortcut to the includes directory.
 *
 * @return string
 */
function get_includes_path() {
	return get_stylesheet_directory() . '/inc/';
}

/**
 * Shortcut to the views directory.
 *
 * @return string
 */
function get_views_path() {
	return get_stylesheet_directory() . '/views/';
}

/**
 * Admin.
 */
require get_includes_path() . 'admin.php';

/**
 * Capabilities.
 */
require get_includes_path() . 'capabilities.php';

/**
 * Taxonomies.
 */
require get_includes_path() . 'taxonomy.php';

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
		get_build_url() . 'style/style-index.css',
		array( 'wporg-parent-2021-style', 'wporg-global-fonts' ),
		filemtime( get_build_path() . 'style/style-index.css' )
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
	return array(
		'learn' => array(
			array(
				'label' => __( 'User', 'wporg-learn' ),
				'url'   => '/learning-pathway/user/',
			),
			array(
				'label' => __( 'Designer', 'wporg-learn' ),
				'url'   => '/learning-pathway/designer/',
			),
			array(
				'label' => __( 'Contributor', 'wporg-learn' ),
				'url'   => '/learning-pathway/contributor/',
			),
			array(
				'label' => __( 'Developer', 'wporg-learn' ),
				'url'   => '/learning-pathway/developer/',
			),
		),
	);
}
