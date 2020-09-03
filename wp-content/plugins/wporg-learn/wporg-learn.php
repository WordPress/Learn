<?php
/**
 * Plugin name: WordPress.org Learn
 * Description: Functionality for learn.wordpress.org. See also the wporg-learn-2020 theme.
 * Version:     1.0.0
 * Author:      WordPress.org
 * Author URI:  http://wordpress.org/
 * License:     GPLv2 or later
 */

require_once dirname( __FILE__ ) . '/inc/blocks.php';
require_once dirname( __FILE__ ) . '/inc/class-markdown-import.php';
require_once dirname( __FILE__ ) . '/inc/class-shortcodes.php';
require_once dirname( __FILE__ ) . '/inc/post-meta.php';
require_once dirname( __FILE__ ) . '/inc/post-type.php';
require_once dirname( __FILE__ ) . '/inc/taxonomy.php';

/**
 * Registry of actions and filters
 */
add_action( 'init', 'WPORG_Learn\Post_Type\register' );
add_action( 'init', 'WPORG_Learn\Post_Meta\register' );
add_action( 'init', 'WPORG_Learn\Taxonomy\register' );
add_action( 'init', array( 'WPOrg_Learn\Shortcodes', 'action_init' ) );

add_action( 'init', 'WPORG_Learn\Blocks\workshop_details_init' );
add_action( 'enqueue_block_editor_assets', 'WPORG_Learn\Blocks\enqueue_block_style_assets' );
add_action( 'wp_enqueue_scripts', 'WPORG_Learn\Blocks\enqueue_block_style_assets' );
add_action( 'add_meta_boxes', 'WPORG_Learn\Post_Meta\add_workshop_metaboxes' );
add_action( 'save_post_wporg_workshop', 'WPORG_Learn\Post_Meta\save_workshop_metabox_fields', 10, 2 );
add_filter( 'excerpt_length', 'theme_slug_excerpt_length', 999 );

/**
 * Register scripts and styles for 3rd party libraries.
 *
 * @return void
 */
function wporg_learn_register_thirdparty_assets() {
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

add_action( 'admin_enqueue_scripts', 'wporg_learn_register_thirdparty_assets', 9 );
add_action( 'wp_enqueue_scripts', 'wporg_learn_register_thirdparty_assets', 9 );

/**
 * Filter the excerpt length to 50 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function theme_slug_excerpt_length( $length ) {
	global $post;

	if ( is_admin() ) {
		return $length;
	}

	if ( 'workshop' === $post->post_type ) {
		return 35;
	}

	return 25;
}
