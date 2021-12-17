<?php

namespace WPOrg_Learn\Blocks;

use Error;
use Sensei_Lesson;
use function WordPressdotorg\Locales\get_locale_name_from_code;
use function WPOrg_Learn\{get_build_path, get_build_url, get_views_path};
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
		'workshop-details-style',
		get_build_url() . 'style-workshop-details.css',
		array(),
		filemtime( get_build_path() . 'style-workshop-details.css' )
	);

	register_block_type( 'wporg-learn/workshop-details', array(
		'editor_script'   => 'workshop-details-editor-script',
		'style'           => 'workshop-details-style',
		'render_callback' => __NAMESPACE__ . '\workshop_details_render_callback',
	) );
}

/**
 * Render the block content (html) on the frontend of the site.
 *
 * @param array  $attributes
 * @param string $content
 * @return string HTML output used by the block
 */
function workshop_details_render_callback( $attributes, $content ) {
	$post      = get_post();
	$topic_ids = wp_get_post_terms( $post->ID, 'topic', array( 'fields' => 'ids' ) );
	$level     = wp_get_post_terms( $post->ID, 'level', array( 'fields' => 'names' ) );
	$captions  = get_post_meta( $post->ID, 'video_caption_language' );

	$version_ids    = wp_get_post_terms( $post->ID, 'wporg_wp_version', array( 'fields' => 'ids' ) );
	$version_names  = wp_get_post_terms( $post->ID, 'wporg_wp_version', array( 'fields' => 'names' ) );

	$topic_names = array();
	foreach ( $topic_ids as $id ) {
		$topic_names[] = get_term( $id )->name;
	}

	$fields = array(
		'length' => array(
			'label' => __( 'Length', 'wporg-learn' ),
			'param' => array(),
			'value' => array( get_workshop_duration( $post, 'string' ) ),
		),
		'topic' => array(
			'label' => __( 'Topic', 'wporg-learn' ),
			'param' => $topic_ids,
			'value' => $topic_names,
		),
		'wp_version' => array(
			'label' => __( 'Related Version', 'wporg-learn' ),
			'param' => $version_ids,
			'value' => $version_names,
		),
		'level' => array(
			'label' => __( 'Level', 'wporg-learn' ),
			'param' => array(),
			'value' => $level,
		),
		'language' => array(
			'label' => __( 'Language', 'wporg-learn' ),
			'param' => array( $post->video_language ),
			'value' => array( esc_html( get_locale_name_from_code( $post->video_language, 'native' ) ) ),
		),
		'captions' => array(
			'label' => __( 'Subtitles', 'wporg-learn' ),
			'param' => $captions,
			'value' => array_map(
				function( $caption_lang ) {
					return esc_html( get_locale_name_from_code( $caption_lang, 'native' ) );
				},
				$captions
			),
		),
	);

	// Remove fields with empty values.
	$fields = array_filter( $fields, function( $data ) {
		return $data['value'];
	} );

	$lesson_id = get_post_meta( $post->ID, 'linked_lesson_id', true );
	$quiz_url = '';
	if ( $lesson_id && Sensei_Lesson::lesson_quiz_has_questions( $lesson_id ) ) {
		$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );
		if ( $quiz_id ) {
			$quiz_url = get_permalink( $quiz_id );
		}
	}

	ob_start();
	require get_views_path() . 'block-workshop-details.php';

	return ob_get_clean();
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
