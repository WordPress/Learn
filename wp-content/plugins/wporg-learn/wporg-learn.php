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

define( __NAMESPACE__ . '\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_URL', plugins_url( '/', __FILE__ ) );

/**
 * Actions and filters.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_files' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\register_thirdparty_assets', 1 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_thirdparty_assets', 1 );

/**
 * Shortcut to the build directory.
 *
 * @return string
 */
function get_build_path() {
	return PLUGIN_DIR . 'build/';
}

/**
 * Shortcut to the build URL.
 *
 * @return string
 */
function get_build_url() {
	return PLUGIN_URL . 'build/';
}

/**
 * Shortcut to the includes directory.
 *
 * @return string
 */
function get_includes_path() {
	return PLUGIN_DIR . 'inc/';
}

/**
 * Shortcut to the views directory.
 *
 * @return string
 */
function get_views_path() {
	return PLUGIN_DIR . 'views/';
}

/**
 * Load the other PHP files for the plugin.
 *
 * @return void
 */
function load_files() {
	require_once get_includes_path() . 'admin.php';
	require_once get_includes_path() . 'blocks.php';
	require_once get_includes_path() . 'capabilities.php';
	require_once get_includes_path() . 'class-markdown-import.php';
	require_once get_includes_path() . 'events.php';
	require_once get_includes_path() . 'form.php';
	require_once get_includes_path() . 'i18n.php';
	require_once get_includes_path() . 'locale.php';
	require_once get_includes_path() . 'post-meta.php';
	require_once get_includes_path() . 'post-type.php';
	require_once get_includes_path() . 'profiles.php';
	require_once get_includes_path() . 'sensei.php';
	require_once get_includes_path() . 'taxonomy.php';
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
		'1.0.8'
	);

	wp_register_style(
		'select2',
		plugins_url( '/3rd-party/selectWoo/css/selectWoo.min.css', __FILE__ ),
		array(),
		'1.0.8'
	);

	if ( 'enqueue_block_editor_assets' === current_action() ) {
		global $typenow;
		if ( 'wporg_workshop' === $typenow ) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'select2' );
		}
	}
}
