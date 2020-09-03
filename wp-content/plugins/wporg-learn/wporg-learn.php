<?php
/**
 * Plugin name: WordPress.org Learn
 * Description: Functionality for learn.wordpress.org. See also the wporg-learn-2020 theme.
 * Version:     1.0.0
 * Author:      WordPress.org
 * Author URI:  http://wordpress.org/
 * License:     GPLv2 or later
 */

namespace WPOrg_Learn;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_files' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_thirdparty_assets', 9 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_thirdparty_assets', 9 );

/**
 * Load the other PHP files for the plugin.
 *
 * @return void
 */
function load_files() {
	require_once dirname( __FILE__ ) . '/inc/blocks.php';
	require_once dirname( __FILE__ ) . '/inc/class-markdown-import.php';
	require_once dirname( __FILE__ ) . '/inc/class-shortcodes.php';
	require_once dirname( __FILE__ ) . '/inc/post-meta.php';
	require_once dirname( __FILE__ ) . '/inc/post-type.php';
	require_once dirname( __FILE__ ) . '/inc/taxonomy.php';
}

/**
 * Register scripts and styles for 3rd party libraries.
 *
 * @return void
 */
function register_thirdparty_assets() {
	wp_register_script(
		'select2',
		plugins_url( '/3rd-party/selectWoo/js/selectWoo.min.js', __FILE__ ),
		array( 'jquery' ),
		'1.0.8',
		true
	);

	wp_register_style(
		'select2',
		plugins_url( '/3rd-party/selectWoo/css/selectWoo.min.css', __FILE__ ),
		array(),
		'1.0.8'
	);
}
