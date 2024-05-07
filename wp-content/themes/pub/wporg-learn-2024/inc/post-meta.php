<?php

namespace WordPressdotorg\Theme\Learn_2024\Post_Meta;

use WP_Post;
use function WordPressdotorg\Locales\{ get_locales_with_english_names };
use function WordPressdotorg\Theme\Learn_2024\{ get_build_path, get_build_url, get_views_path };

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_meeting_metaboxes' );
add_action( 'admin_footer', __NAMESPACE__ . '\render_locales_list' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_editor_assets' );

/**
 * Register all post meta keys.
 */
function register() {
	register_misc_meta();
}

/**
 * Register other post meta keys.
 *
 * For multiple post types, for example.
 */
function register_misc_meta() {
	// Language field.
	$post_types = array( 'meeting', 'course', 'lesson' );
	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'language',
			array(
				'description'       => __( 'The language for the content.', 'wporg_learn' ),
				'type'              => 'string',
				'single'            => true,
				'default'           => 'en_US',
				'sanitize_callback' => __NAMESPACE__ . '\sanitize_locale',
				'show_in_rest'      => true,
			)
		);
	}
}

/**
 * Add meta boxes to the Edit Meeting screen.
 */
function add_meeting_metaboxes( $post_type = '' ) {
	add_meta_box(
		'meeting-language',
		__( 'Language', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_meeting_language',
		'meeting',
		'side'
	);
}

/**
 * Render the Meeting Language meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_meeting_language( WP_Post $post ) {
	$locales  = get_locales_with_english_names();
	$language = get_post_meta( $post->ID, 'language', true ) ?: '';

	require get_views_path() . 'metabox-meeting-language.php';
}

/**
 * Sanitize a locale value.
 *
 * @param string $meta_value
 * @param string $meta_key
 * @param string $object_type
 * @param string $object_subtype
 *
 * @return string
 */
function sanitize_locale( $meta_value, $meta_key, $object_type, $object_subtype ) {
	$meta_value = trim( $meta_value );
	$locales    = array_keys( get_locales_with_english_names() );

	if ( ! in_array( $meta_value, $locales, true ) ) {
		return '';
	}

	return $meta_value;
}

/**
 * Render the locales list for the language meta block.
 */
function render_locales_list() {
	global $typenow;

	$post_types_with_language = array( 'meeting', 'course', 'lesson' );
	if ( in_array( $typenow, $post_types_with_language, true ) ) {
		$locales = get_locales_with_english_names();

		require get_views_path() . 'locales-list.php';
	}
}

/**
 * Enqueue scripts for the block editor.
 */
function enqueue_editor_assets() {
	enqueue_language_meta_assets();
}

/**
 * Enqueue scripts for the language meta block.
 */
function enqueue_language_meta_assets() {
	global $typenow;

	$post_types_with_language = array( 'course', 'lesson' );
	if ( in_array( $typenow, $post_types_with_language, true ) ) {
		$script_asset_path = get_build_path() . 'language-meta.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			wp_die( 'You need to run `yarn start` or `yarn build` to build the required assets.' );
		}

		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'wporg-learn-language-meta',
			get_build_url() . 'language-meta.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations( 'wporg-learn-language-meta', 'wporg-learn' );
	}
}
