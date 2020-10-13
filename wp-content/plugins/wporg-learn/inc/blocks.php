<?php

namespace WPOrg_Learn\Blocks;

use Error;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\{ get_build_path, get_build_url };
use function WPOrg_Learn\Form\render_workshop_application_form;
use function WPOrg_Learn\Post_Meta\get_workshop_duration;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register_types' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_style_assets' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_block_style_assets' );

/**
 * Register block types.
 *
 * @return void
 */
function register_types() {
	register_workshop_details();
	register_workshop_application_form();
}

/**
 * Register Workshop Details block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_workshop_details() {
	$script_asset_path = get_build_path() . 'workshop-details.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-details" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-details-editor-script',
		get_build_url() . 'workshop-details.js',
		$script_asset['dependencies'],
		$script_asset['version']
	);

	wp_register_style(
		'workshop-details-editor-style',
		get_build_url() . 'workshop-details.css',
		array(),
		filemtime( get_build_path() . 'workshop-details.css' )
	);

	wp_register_style(
		'workshop-details-style',
		get_build_url() . 'style-workshop-details.css',
		array(),
		filemtime( get_build_path() . 'style-workshop-details.css' )
	);

	register_block_type( 'wporg-learn/workshop-details', array(
		'editor_script'   => 'workshop-details-editor-script',
		'editor_style'    => 'workshop-details-editor-style',
		'style'           => 'workshop-details-style',
		'render_callback' => __NAMESPACE__ . '\workshop_details_render_callback',
	) );
}

/**
 * Build the html output based on input fields
 *
 * @param array $fields
 * @return string HTML output.
 */
function get_workshop_details_html_output( $fields ) {
	$output = '<ul class="wp-block-wporg-learn-workshop-details">';

	foreach ( $fields as $key => $value ) {
		$output .= sprintf(
			'<li><b>%1$s</b><span>%2$s</span></li>',
			$key,
			$value
		);
	}

	$output .= '</ul>';

	return $output;
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function workshop_details_render_callback( $attributes, $content ) {
	$post     = get_post();
	$topics   = wp_get_post_terms( $post->ID, 'topic', array( 'fields' => 'names' ) );
	$level    = wp_get_post_terms( $post->ID, 'level', array( 'fields' => 'names' ) );
	$captions = get_post_meta( $post->ID, 'video_caption_language' );

	$fields = array(
		__( 'Length', 'wporg-learn' )   => get_workshop_duration( $post, 'string' ),
		__( 'Topic', 'wporg-learn' )    => implode( ', ', array_map( 'esc_html', $topics ) ),
		__( 'Level', 'wporg-learn' )    => implode( ', ', array_map( 'esc_html', $level ) ),
		__( 'Language', 'wporg-learn' ) => esc_html( get_locale_name_from_code( $post->video_language, 'native' ) ),
		__( 'Captions', 'wporg-learn' ) => implode(
			', ',
			array_map(
				function( $caption_lang ) {
					return esc_html( get_locale_name_from_code( $caption_lang, 'native' ) );
				},
				$captions
			)
		),
	);

	// Remove empty fields.
	$fields_to_output = array_filter( $fields );

	return get_workshop_details_html_output( $fields_to_output );
}

/**
 * Register Workshop Application Form block type and related assets.
 *
 * @throws Error If the build files are not found.
 */
function register_workshop_application_form() {
	$script_asset_path = get_build_path() . 'workshop-application-form.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-application-form" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-application-form-editor-script',
		get_build_url() . 'workshop-application-form.js',
		$script_asset['dependencies'],
		$script_asset['version']
	);

	$script_asset_path = get_build_path() . 'form.asset.php';
	if ( ! is_readable( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-application-form-script',
		get_build_url() . 'form.js',
		array_merge( $script_asset['dependencies'], array( 'jquery', 'select2' ) ),
		$script_asset['version'],
		true
	);

	register_block_type( 'wporg-learn/workshop-application-form', array(
		'editor_script'   => 'workshop-application-form-editor-script',
		'script'          => 'workshop-application-form-script',
		'style'           => 'select2',
		'render_callback' => __NAMESPACE__ . '\workshop_application_form_render_callback',
	) );
}

/**
 * Render the Workshop Application Form block markup.
 *
 * @return string
 */
function workshop_application_form_render_callback() {
	return render_workshop_application_form();
}

/**
 * Enqueue scripts and stylesheets for custom block styles.
 *
 * @throws Error If the build files are not found.
 */
function enqueue_block_style_assets() {
	if ( is_admin() ) {
		$script_asset_path = get_build_path() . 'block-styles.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for block styles first.'
			);
		}

		$script_asset = require $script_asset_path;
		wp_enqueue_script(
			'wporg-learn-block-styles',
			get_build_url() . 'block-styles.js',
			$script_asset['dependencies'],
			$script_asset['version']
		);
	}

	wp_enqueue_style(
		'wporg-learn-block-styles',
		get_build_url() . 'style-block-styles.css',
		array(),
		filemtime( get_build_path() . 'style-block-styles.css' )
	);
}
