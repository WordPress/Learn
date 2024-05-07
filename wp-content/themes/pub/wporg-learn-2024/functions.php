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
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_filter( 'wporg_block_navigation_menus', __NAMESPACE__ . '\add_site_navigation_menus' );

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
 * Provide a list of local navigation menus.
 */
function add_site_navigation_menus( $menus ) {
	return array(
		'learn' => array(
			array(
				'label' => __( 'User', 'wporg-learn' ),
				'url'   => '/learning-pathways/user/',
			),
			array(
				'label' => __( 'Designer', 'wporg-learn' ),
				'url'   => '/learning-pathways/designer/',
			),
			array(
				'label' => __( 'Contributor', 'wporg-learn' ),
				'url'   => '/learning-pathways/contributor/',
			),
			array(
				'label' => __( 'Developer', 'wporg-learn' ),
				'url'   => '/learning-pathways/developer/',
			),
		),
	);
}
