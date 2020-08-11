<?php

namespace WPOrg_Learn\Post_Meta;

use DateTime, DateInterval;
use WP_Post;

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
		'presenter_wporg_username',
		array(
			'description'       => __( 'The WordPress.org user name of a presenter for this workshop.', 'wporg_learn' ),
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

/**
 * Get the duration of a workshop in a specified format.
 *
 * @param WP_Post $workshop The workshop post to get the duration for.
 * @param string  $format   Optional. The format to return the duration in. 'raw', 'interval', or 'string'.
 *                          Default 'raw'.
 *
 * @return int|DateInterval|string
 */
function get_workshop_duration( WP_Post $workshop, $format = 'raw' ) {
	$raw_duration = $workshop->duration ? absint( $workshop->duration ) : 0;
	$interval = date_diff( new DateTime( '@0' ), new DateTime( "@$raw_duration" ) ); // The '@' ignores timezone.
	$return = null;

	switch ( $format ) {
		case 'interval':
			$return = $interval;
			break;
		case 'string':
			$return = human_readable_duration( $interval->format( '%H:%I:%S' ) );
			break;
		case 'raw':
		default:
			$return = $raw_duration;
			break;
	}

	return $return;
}

/**
 * Add meta boxes to the Edit Workshop screen.
 *
 * Todo these should be replaced with block editor panels.
 */
function add_workshop_metaboxes() {
	add_meta_box(
		'workshop-details',
		__( 'Workshop Details', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_workshop_details',
		'wporg_workshop',
		'side'
	);

	add_meta_box(
		'workshop-presenters',
		__( 'Presenters', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_workshop_presenters',
		'wporg_workshop',
		'side'
	);
}

/**
 * Render the Workshop Details meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_details( WP_Post $post ) {
	$duration_interval = get_workshop_duration( $post, 'interval' );
	$captions = get_post_meta( $post->ID, 'video_caption_language' ) ?: array();

	require dirname( dirname( __FILE__ ) ) . '/views/metabox-workshop-details.php';
}

/**
 * Render the Presenters meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_presenters( WP_Post $post ) {
	$presenters = get_post_meta( $post->ID, 'presenter_wporg_username' ) ?: array();

	require dirname( dirname( __FILE__ ) ) . '/views/metabox-workshop-presenters.php';
}

/**
 * Update the post meta values from the meta box fields when the post is saved.
 *
 * @param int $post_id
 * @param WP_Post $post
 */
function save_workshop_metabox_fields( $post_id, WP_Post $post ) {
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$duration = filter_input( INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
	if ( isset( $duration['h'], $duration['m'], $duration['s'] ) ) {
		$duration = $duration['h'] * HOUR_IN_SECONDS + $duration['m'] * MINUTE_IN_SECONDS + $duration['s'];
		update_post_meta( $post_id, 'duration', $duration );
	}

	$presenter_wporg_username = filter_input( INPUT_POST, 'presenter-wporg-username' );
	$usernames = array_map( 'trim', explode( ',', $presenter_wporg_username ) );
	delete_post_meta( $post_id, 'presenter_wporg_username' );
	foreach( $usernames as $username ) {
		add_post_meta( $post_id, 'presenter_wporg_username', $username );
	}

	$video_language = filter_input( INPUT_POST, 'video-language' );
	update_post_meta( $post_id, 'video_language', $video_language );

	$video_caption_language = filter_input( INPUT_POST, 'video-caption-language' );
	$captions = array_map( 'trim', explode( ',', $video_caption_language ) );
	delete_post_meta( $post_id, 'video_caption_language' );
	foreach( $captions as $caption ) {
		add_post_meta( $post_id, 'video_caption_language', $caption );
	}
}
