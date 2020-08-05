<?php

namespace WPOrg_Learn\Post_Meta;

defined( 'WPINC' ) || die();

/**
 * Register all post meta keys.
 */
function register() {
	register_workshop_meta();
}

/**
 * Register post meta keys for workshops.
 */
function register_workshop_meta() {
	$post_type = 'wporg_workshop';

	register_post_meta(
		$post_type,
		'duration',
		array(
			'description'       => __( 'The duration in seconds of the workshop. Should be converted to a human readable string for display.', 'wporg_learn' ),
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'facilitator_wporg_username',
		array(
			'description'       => __( 'The WordPress.org user name of a facilitator for this workshop.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => false,
			'sanitize_callback' => 'sanitize_user',
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'video_language',
		array(
			'description'       => __( 'The language that the workshop is presented in.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => '', // todo
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'video_caption_language',
		array(
			'description'       => __( 'A language for which captions are available for the workshop video.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => false,
			'sanitize_callback' => '', // todo
			'show_in_rest'      => true,
		)
	);
}
