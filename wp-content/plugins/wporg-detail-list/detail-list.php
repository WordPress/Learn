<?php
/**
 * Plugin Name:     Detail List
 * Description:     Example block written with ESNext standard and JSX support – build step required.
 * Version:         0.1.0
 * Author:          The WordPress Contributors
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     create-block
 *
 * @package         create-block
 */


 /**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the calendar JS.
 */
function create_block_detail_list_block_render_callback( $attributes, $content ) {
	$post = get_post();
	$topics = wp_get_post_terms( $post->ID, 'topic' );
	$level = wp_get_post_terms( $post->ID, 'level' );

	return sprintf(
        '<ul class="wp-block-wporg-learn-detail-list">
			<li><b>Length</b><span>%1$s</span></li>
			<li><b>Topic</b><span>%2$s</span></li>
			<li><b>Level</b><span>%3$s</span></li>
			<li><b>Language</b><span>%4$s</span></li>
			<li><b>Captions</b><span>%5$s</span></li>
		</ul>',
		$post->duration,
		$topics && $topics[0] ? $topics[0]->name : '',
		$level && $level[0] ? $level[0]->name : '',
		$post->video_language,
		$post->video_caption_language,
    );
}

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function create_block_detail_list_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "wporg-learn/detail-list" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'create-block-detail-list-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);

	$editor_css = 'build/index.css';
	wp_register_style(
		'create-block-detail-list-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'create-block-detail-list-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type( 'wporg-learn/detail-list', array(
		'editor_script' => 'create-block-detail-list-block-editor',
		'editor_style'  => 'create-block-detail-list-block-editor',
		'style'         => 'create-block-detail-list-block',
		'render_callback' => 'create_block_detail_list_block_render_callback'
	) );
}
add_action( 'init', 'create_block_detail_list_block_init' );
