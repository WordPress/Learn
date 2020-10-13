<?php

namespace WPOrg_Learn\Post_Meta;

use DateTime, DateInterval;
use WP_Post;
use function WordPressdotorg\Locales\{ get_locales_with_english_names };
use function WPOrg_Learn\get_views_path;
use function WPOrg_Learn\Form\get_workshop_application_field_schema;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_workshop_metaboxes' );
add_action( 'save_post_wporg_workshop', __NAMESPACE__ . '\save_workshop_metabox_fields', 10, 2 );

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
			'default'           => 'en_US',
			'sanitize_callback' => __NAMESPACE__ . '\sanitize_locale',
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
			'sanitize_callback' => __NAMESPACE__ . '\sanitize_locale',
			'show_in_rest'      => true,
		)
	);
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
	if ( 'wporg_workshop' !== $object_subtype ) {
		return $meta_value;
	}

	$meta_value = trim( $meta_value );
	$locales = array_keys( get_locales_with_english_names() );

	if ( ! in_array( $meta_value, $locales, true ) ) {
		return '';
	}

	return $meta_value;
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
	$interval     = date_diff( new DateTime( '@0' ), new DateTime( "@$raw_duration" ) ); // The '@' ignores timezone.
	$return       = null;

	switch ( $format ) {
		case 'interval':
			$return = $interval;
			break;
		case 'string':
			if ( $interval->d > 0 ) {
				$return = human_time_diff( 0, $interval->d * DAY_IN_SECONDS );
			} elseif ( $interval->h > 0 ) {
				$hours = human_time_diff( 0, $interval->h * HOUR_IN_SECONDS );
				$return = $hours;

				if ( $interval->i > 0 ) {
					$minutes = human_time_diff( 0, $interval->i * MINUTE_IN_SECONDS );
					$return  = sprintf(
						// translators: 1 is a string like "2 hours". 2 is a string like "20 mins".
						_x( '%1$s, %2$s', 'hours and minutes', 'wporg-learn' ),
						$hours,
						$minutes
					);
				}
			} elseif ( $interval->i > 0 ) {
				$return = human_time_diff( 0, $interval->i * MINUTE_IN_SECONDS );
			} elseif ( $interval->s > 0 ) {
				$return = human_time_diff( 0, $interval->s );
			}
			break;
		case 'raw':
		default:
			$return = $raw_duration;
			break;
	}

	return $return;
}

/**
 * Get a list of locales that are associated with at least one workshop.
 *
 * @param string $meta_key
 * @param string $label_language
 *
 * @return array
 */
function get_available_workshop_locales( $meta_key, $label_language = 'english' ) {
	global $wpdb;

	$results = $wpdb->get_col( $wpdb->prepare(
		"
			SELECT DISTINCT meta_value
			FROM $wpdb->postmeta
			WHERE meta_key = %s
			ORDER BY meta_value ASC
		",
		$meta_key
	) );

	if ( empty( $results ) ) {
		return array();
	}

	$available_locales = array_fill_keys( $results, '' );

	$locale_fn = "\WordPressdotorg\Locales\get_locales_with_{$label_language}_names";
	$locales   = $locale_fn();

	return array_intersect_key( $locales, $available_locales );
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

	add_meta_box(
		'workshop-application',
		__( 'Original Application', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_workshop_application',
		'wporg_workshop',
		'advanced'
	);
}

/**
 * Render the Workshop Details meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_details( WP_Post $post ) {
	$duration_interval = get_workshop_duration( $post, 'interval' );
	$locales           = get_locales_with_english_names();
	$captions          = get_post_meta( $post->ID, 'video_caption_language' ) ?: array();

	require get_views_path() . 'metabox-workshop-details.php';
}

/**
 * Render the Presenters meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_presenters( WP_Post $post ) {
	$presenters = get_post_meta( $post->ID, 'presenter_wporg_username' ) ?: array();

	require get_views_path() . 'metabox-workshop-presenters.php';
}

/**
 * Render the Original Application meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_application( WP_Post $post ) {
	$schema = get_workshop_application_field_schema();
	$application = wp_parse_args(
		get_post_meta( $post->ID, 'original_application', true ) ?: array(),
		wp_list_pluck( $schema['properties'], 'default' )
	);

	require get_views_path() . 'metabox-workshop-application.php';
}

/**
 * Update the post meta values from the meta box fields when the post is saved.
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function save_workshop_metabox_fields( $post_id, WP_Post $post ) {
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// This nonce field is rendered in the Workshop Details metabox.
	$nonce = filter_input( INPUT_POST, 'workshop-metabox-nonce' );
	if ( ! wp_verify_nonce( $nonce, 'workshop-metaboxes' ) ) {
		return;
	}

	$duration = filter_input( INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
	if ( isset( $duration['h'], $duration['m'], $duration['s'] ) ) {
		$duration = $duration['h'] * HOUR_IN_SECONDS + $duration['m'] * MINUTE_IN_SECONDS + $duration['s'];
		update_post_meta( $post_id, 'duration', $duration );
	}

	$video_language = filter_input( INPUT_POST, 'video-language' );
	update_post_meta( $post_id, 'video_language', $video_language );

	$captions = filter_input( INPUT_POST, 'video-caption-language', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	delete_post_meta( $post_id, 'video_caption_language' );
	if ( is_array( $captions ) ) {
		foreach ( $captions as $caption ) {
			add_post_meta( $post_id, 'video_caption_language', $caption );
		}
	}

	$presenter_wporg_username = filter_input( INPUT_POST, 'presenter-wporg-username' );
	$usernames                = array_map( 'trim', explode( ',', $presenter_wporg_username ) );
	delete_post_meta( $post_id, 'presenter_wporg_username' );
	if ( is_array( $usernames ) ) {
		foreach ( $usernames as $username ) {
			add_post_meta( $post_id, 'presenter_wporg_username', $username );
		}
	}
}
