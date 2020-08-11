<?php

namespace WPOrg_Learn\Blocks;

use function WPOrg_Learn\Post_Meta\get_workshop_duration;

defined( 'WPINC' ) || die();

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 * @throws Error If the build files are not found.
 */
function workshop_details_init() {
	$dir = dirname( __DIR__ );

	$script_asset_path = "$dir/build/workshop-details.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-details" block first.'
		);
	}

	$script_asset = require $script_asset_path;
	wp_register_script(
		'workshop-details-editor-script',
		plugins_url( 'build/workshop-details.js', 'wporg-learn/wporg-learn.php' ),
		$script_asset['dependencies'],
		$script_asset['version']
	);

	$editor_css = 'build/workshop-details.css';
	wp_register_style(
		'workshop-details-editor-style',
		plugins_url( $editor_css, 'wporg-learn/wporg-learn.php' ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-workshop-details.css';
	wp_register_style(
		'workshop-details-style',
		plugins_url( $style_css, 'wporg-learn/wporg-learn.php' ),
		array(),
		filemtime( "$dir/$style_css" )
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
		$output .= sprintf( '<li><b>%1$s</b><span>%2$s</span></li>', $key, $value );
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
		__( 'Language', 'wporg-learn' ) => esc_html( $post->video_language ),
		__( 'Captions', 'wporg-learn' ) => implode( ', ', array_map( 'esc_html', $captions ) ),
	);

	// Remove empty fields.
	$fields_to_output = array_filter( $fields );

	return get_workshop_details_html_output( $fields_to_output );
}

/**
 * Enqueue scripts and stylesheets for custom block styles.
 *
 * @throws Error If the build files are not found.
 */
function enqueue_block_style_assets() {
	$dir = dirname( __DIR__ );

	if ( is_admin() ) {
		$script_asset_path = "$dir/build/workshop-details.asset.php";
		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for the "wporg-learn/workshop-details" block first.'
			);
		}

		$script_asset = require $script_asset_path;
		wp_enqueue_script(
			'wporg-learn-block-styles',
			plugins_url( 'build/block-styles.js', 'wporg-learn/wporg-learn.php' ),
			$script_asset['dependencies'],
			$script_asset['version']
		);
	}

	wp_enqueue_style(
		'wporg-learn-block-styles',
		plugins_url( 'build/style-block-styles.css', 'wporg-learn/wporg-learn.php' ),
		array(),
		filemtime( $dir . '/build/style-block-styles.css' )
	);
}
