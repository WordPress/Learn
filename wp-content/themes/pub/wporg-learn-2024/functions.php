<?php

namespace WordPressdotorg\Theme\Learn_2024;

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
		get_stylesheet_directory_uri() . '/build/style/style-index.css',
		array( 'wporg-parent-2021-style', 'wporg-global-fonts' ),
		filemtime( __DIR__ . '/build/style/style-index.css' )
	);
}

/**
 * Provide a list of local navigation menus.
 */
function add_site_navigation_menus( $menus ) {
	return array(
		'learn' => array(
			array(
				'label' => __( 'Courses', 'wporg-learn' ),
				'url' => '/courses/',
			),
			array(
				'label' => __( 'Lessons', 'wporg-learn' ),
				'url' => '/lessons/',
			),
			array(
				'label' => __( 'Learning Pathways', 'wporg-learn' ),
				'url' => '/learning-pathways/',
			),
			array(
				'label' => __( 'Contribute', 'wporg-learn' ),
				'url' => '/contribute/',
			),
			array(
				'label' => __( 'Instruct', 'wporg-learn' ),
				'url' => '/instruct/',
			),
		),
	);
}

