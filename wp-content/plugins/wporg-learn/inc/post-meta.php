<?php

namespace WPOrg_Learn\Post_Meta;

use DateTime, DateInterval;
use WP_Post;
use function WordPressdotorg\Locales\{ get_locales_with_english_names };
use function WPOrg_Learn\{ get_build_path, get_build_url, get_views_path };
use function WPOrg_Learn\Form\get_workshop_application_field_schema;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_lesson_plan_metaboxes' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_workshop_metaboxes' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\add_meeting_metaboxes' );
add_action( 'save_post_lesson-plan', __NAMESPACE__ . '\save_lesson_plan_metabox_fields' );
add_action( 'save_post_wporg_workshop', __NAMESPACE__ . '\save_workshop_meta_fields' );
add_action( 'save_post_meeting', __NAMESPACE__ . '\save_meeting_metabox_fields' );
add_action( 'admin_footer', __NAMESPACE__ . '\render_locales_list' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_editor_assets' );

/**
 * Register all post meta keys.
 */
function register() {
	register_lesson_plan_meta();
	register_workshop_meta();
	register_misc_meta();
}

/**
 * Register post meta keys for lesson plans.
 */
function register_lesson_plan_meta() {
	$post_type = 'lesson-plan';

	register_post_meta(
		$post_type,
		'slides_view_url',
		array(
			'description'       => __( 'A URL for viewing lesson plan slides.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'slides_download_url',
		array(
			'description'       => __( 'A URL for downloading lesson plan slides.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		)
	);
}

/**
 * Register post meta keys for workshops.
 */
function register_workshop_meta() {
	$post_type = 'wporg_workshop';

	register_post_meta(
		$post_type,
		'video_url',
		array(
			'description'       => __( "The URL of the Workshop's video.", 'wporg_learn' ),
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		)
	);

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
		'other_contributor_wporg_username',
		array(
			'description'       => __( 'The WordPress.org user name of "other contributor" for this workshop.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => false,
			'sanitize_callback' => 'sanitize_user',
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'video_caption_language',
		array(
			'description'       => __( 'A language for which subtitles are available for the workshop video.', 'wporg_learn' ),
			'type'              => 'string',
			'single'            => false,
			'sanitize_callback' => __NAMESPACE__ . '\sanitize_locale',
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		$post_type,
		'linked_lesson_id',
		array(
			'description'       => __( 'The post ID of a lesson that covers this workshop.', 'wporg_learn' ),
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		)
	);
}

/**
 * Register other post meta keys.
 *
 * For multiple post types, for example.
 */
function register_misc_meta() {
	// Expiration field.
	$post_types = array( 'lesson-plan', 'wporg_workshop', 'course', 'lesson' );
	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'expiration_date',
			array(
				'description'       => __( 'The date when the content of the post may be obsolete.', 'wporg_learn' ),
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => function( $value ) {
					return filter_var( $value, FILTER_SANITIZE_STRING );
				},
				'show_in_rest'      => true,
			)
		);
	}

	// Language field.
	$post_types = array( 'lesson-plan', 'wporg_workshop', 'meeting', 'course', 'lesson' );
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

	// Duration field.
	$post_types = array( 'course', 'lesson' );
	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'_duration',
			array(
				'description'       => __( 'The time required to complete the Course or Lesson.', 'wporg_learn' ),
				'type'              => 'number',
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => function( $value ) {
					return floatval( $value );
				},
				'show_in_rest'      => true,
				'auth_callback'     => function() {
					return current_user_can( 'edit_courses' ) || current_user_can( 'edit_lessons' );
				},
			)
		);
	}
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
 * Get a list of locales that are associated with at least one post of the specified type.
 *
 * @param string $meta_key
 * @param string $post_type
 * @param string $post_status
 * @param string $label_language
 *
 * @return array
 */
function get_available_post_type_locales( $meta_key, $post_type, $post_status, $label_language = 'english' ) {
	global $wpdb;

	$and_post_status = '';
	if ( in_array( $post_status, get_post_stati(), true ) ) {
		$and_post_status = "AND posts.post_status = '$post_status'";
	}

	$results = $wpdb->get_col( $wpdb->prepare(
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $and_post_status only includes $post_status if it matches an allowed string.
		"
		SELECT DISTINCT postmeta.meta_value
		FROM {$wpdb->postmeta} postmeta
			JOIN {$wpdb->posts} posts ON posts.ID = postmeta.post_id AND posts.post_type = %s $and_post_status
		WHERE postmeta.meta_key = %s
	",
		$post_type,
		$meta_key
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
 * Add meta boxes to the Edit Lesson Plan screen.
 *
 * Todo these should be replaced with block editor panels.
 */
function add_lesson_plan_metaboxes() {
	add_meta_box(
		'lesson-plan-slides',
		__( 'Slides', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_lesson_plan_slides',
		'lesson-plan',
		'side'
	);
}

/**
 * Render the Lesson Plan Slides metabox.
 *
 * @param WP_Post $post
 */
function render_metabox_lesson_plan_slides( WP_Post $post ) {
	// The $post var is used in the include file.
	require get_views_path() . 'metabox-lesson-plan-slides.php';
}

/**
 * Update the post meta values from the meta box fields when the post is saved.
 *
 * @param int $post_id
 */
function save_lesson_plan_metabox_fields( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// This nonce field is rendered in the Lesson Plan Slides metabox.
	$nonce = filter_input( INPUT_POST, 'lesson-plan-metabox-nonce' );
	if ( ! wp_verify_nonce( $nonce, 'lesson-plan-metaboxes' ) ) {
		return;
	}

	$view_url = filter_input( INPUT_POST, 'slides-view-url', FILTER_VALIDATE_URL ) ?: '';
	update_post_meta( $post_id, 'slides_view_url', $view_url );

	$download_url = filter_input( INPUT_POST, 'slides-download-url', FILTER_VALIDATE_URL ) ?: '';
	update_post_meta( $post_id, 'slides_download_url', $download_url );

	// This language meta field is rendered in the editor sidebar using a PluginDocumentSettingPanel block,
	// which won't save the field on publish if it has the default value.
	// Our filtering by locale depends on it being set, so we force it to be updated after saving:
	$language         = get_post_meta( $post_id, 'language', true );
	$language_default = 'en_US';
	if ( ! isset( $language ) || $language_default === $language ) {
		update_post_meta( $post_id, 'language', $language_default );
	}
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
		'workshop-other-contributors',
		__( 'Other Contributors', 'wporg_learn' ),
		__NAMESPACE__ . '\render_metabox_workshop_other_contributors',
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
 * Render the Workshop Details meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_details( WP_Post $post ) {
	$duration_interval = get_workshop_duration( $post, 'interval' );
	$locales           = get_locales_with_english_names();
	$captions          = get_post_meta( $post->ID, 'video_caption_language' ) ?: array();
	$all_lessons       = get_posts( array(
		'post_type'      => 'lesson',
		'post_status'    => 'publish',
		'posts_per_page' => 999,
		'orderby'        => 'title',
		'order'          => 'asc',
	) );

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
 * Render the Other Contributors meta box.
 *
 * @param WP_Post $post
 */
function render_metabox_workshop_other_contributors( WP_Post $post ) {
	$other_contributors = get_post_meta( $post->ID, 'other_contributor_wporg_username' ) ?: array();

	require get_views_path() . 'metabox-workshop-other-contributors.php';
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
 * Update the post meta values from the meta fields when the post is saved.
 *
 * @param int $post_id
 */
function save_workshop_meta_fields( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// This nonce field is rendered in the Workshop Details metabox.
	$nonce = filter_input( INPUT_POST, 'workshop-metabox-nonce' );
	if ( ! wp_verify_nonce( $nonce, 'workshop-metaboxes' ) ) {
		return;
	}

	$video_url = filter_input( INPUT_POST, 'video-url', FILTER_SANITIZE_URL );
	update_post_meta( $post_id, 'video_url', $video_url );

	$duration = filter_input( INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
	if ( isset( $duration['h'], $duration['m'], $duration['s'] ) ) {
		$duration = $duration['h'] * HOUR_IN_SECONDS + $duration['m'] * MINUTE_IN_SECONDS + $duration['s'];
		update_post_meta( $post_id, 'duration', $duration );
	}

	$captions = filter_input( INPUT_POST, 'video-caption-language', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	delete_post_meta( $post_id, 'video_caption_language' );
	if ( is_array( $captions ) ) {
		foreach ( $captions as $caption ) {
			add_post_meta( $post_id, 'video_caption_language', $caption );
		}
	}

	$lesson_id = filter_input( INPUT_POST, 'linked-lesson-id', FILTER_SANITIZE_NUMBER_INT );
	update_post_meta( $post_id, 'linked_lesson_id', $lesson_id );

	$presenter_wporg_username = filter_input( INPUT_POST, 'presenter-wporg-username' );
	$presenter_usernames      = array_map( 'trim', explode( ',', $presenter_wporg_username ) );
	delete_post_meta( $post_id, 'presenter_wporg_username' );
	if ( is_array( $presenter_usernames ) ) {
		foreach ( $presenter_usernames as $username ) {
			add_post_meta( $post_id, 'presenter_wporg_username', $username );
		}
	}

	$other_contributor_wporg_username = filter_input( INPUT_POST, 'other-contributor-wporg-username' );
	$other_contributor_usernames      = array_map( 'trim', explode( ',', $other_contributor_wporg_username ) );
	delete_post_meta( $post_id, 'other_contributor_wporg_username' );
	if ( is_array( $other_contributor_usernames ) ) {
		foreach ( $other_contributor_usernames as $username ) {
			add_post_meta( $post_id, 'other_contributor_wporg_username', $username );
		}
	}

	// This language meta field is rendered in the editor sidebar using a PluginDocumentSettingPanel block,
	// which won't save the field on publish if it has the default value.
	// Our custom workshops query for locale prioritized tutorials (see functions.php `wporg_archive_query_prioritize_locale`)
	// depends on it being set, so we force it to be updated after saving:
	$language         = get_post_meta( $post_id, 'language', true );
	$language_default = 'en_US';
	if ( ! isset( $language ) || $language_default === $language ) {
		update_post_meta( $post_id, 'language', $language_default );
	}
}

/**
 * Update the post meta values from the meta box fields when a meeting post is saved.
 *
 * @param int $post_id
 */
function save_meeting_metabox_fields( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// This nonce field is rendered in the Meeting Language metabox.
	$nonce = filter_input( INPUT_POST, 'meeting-metabox-nonce' );
	if ( ! wp_verify_nonce( $nonce, 'meeting-metaboxes' ) ) {
		return;
	}

	$language = filter_input( INPUT_POST, 'meeting-language' );
	update_post_meta( $post_id, 'language', $language );

}

/**
 * Render the locales list for the language meta block.
 */
function render_locales_list() {
	global $typenow;

	$post_types_with_language = array( 'lesson-plan', 'wporg_workshop', 'meeting', 'course', 'lesson' );
	if ( in_array( $typenow, $post_types_with_language, true ) ) {
		$locales = get_locales_with_english_names();

		require get_views_path() . 'locales-list.php';
	}
}

/**
 * Enqueue scripts for the block editor.
 */
function enqueue_editor_assets() {
	enqueue_expiration_date_assets();
	enqueue_language_meta_assets();
	enqueue_duration_meta_assets();
}

/**
 * Enqueue scripts for the expiration data block.
 */
function enqueue_expiration_date_assets() {
	global $typenow;

	$post_types_with_expiration = array( 'lesson-plan', 'wporg_workshop', 'course', 'lesson' );
	if ( in_array( $typenow, $post_types_with_expiration, true ) ) {
		$script_asset_path = get_build_path() . 'expiration-date.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			wp_die( 'You need to run `yarn start` or `yarn build` to build the required assets.' );
		}

		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'wporg-learn-expiration-date',
			get_build_url() . 'expiration-date.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations( 'wporg-learn-expiration-date', 'wporg-learn' );
	}
}


/**
 * Enqueue scripts for the language meta block.
 */
function enqueue_language_meta_assets() {
	global $typenow;

	$post_types_with_language = array( 'lesson-plan', 'wporg_workshop', 'meeting', 'course', 'lesson' );
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

/**
 * Enqueue scripts for the duration meta block.
 */
function enqueue_duration_meta_assets() {
	global $typenow;

	$post_types_with_duration = array( 'course', 'lesson' );
	if ( in_array( $typenow, $post_types_with_duration, true ) ) {
		$script_asset_path = get_build_path() . 'duration-meta.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			wp_die( 'You need to run `yarn start` or `yarn build` to build the required assets.' );
		}

		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'wporg-learn-duration-meta',
			get_build_url() . 'duration-meta.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations( 'wporg-learn-duration-meta', 'wporg-learn' );
	}
}
