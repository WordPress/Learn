<?php

namespace WPOrg_Learn\Activity_Kit_REST;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'rest_api_init', __NAMESPACE__ . '\register_routes' );
add_action( 'init', __NAMESPACE__ . '\maybe_create_events_table' );

/**
 * Register REST API routes for activity kits.
 */
function register_routes() {
	register_rest_route(
		'activity-kits/v1',
		'/track',
		array(
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\handle_track',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'action'  => array(
					'required' => true,
					'type'     => 'string',
					'enum'     => array( 'view', 'download' ),
				),
			),
		)
	);

	register_rest_route(
		'activity-kits/v1',
		'/stats',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\handle_stats',
			'permission_callback' => function() {
				return current_user_can( 'manage_options' );
			},
			'args'                => array(
				'metric' => array(
					'default' => 'both',
					'enum'    => array( 'both', 'views', 'downloads' ),
				),
				'range'  => array(
					'default' => 'all',
					'enum'    => array( '7d', '30d', '90d', 'all' ),
				),
				'kit'    => array(
					'default'           => '',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}

/**
 * Handle POST /activity-kits/v1/track
 *
 * @param \WP_REST_Request $request
 * @return \WP_REST_Response|\WP_Error
 */
function handle_track( $request ) {
	$post_id = $request->get_param( 'post_id' );
	$action  = $request->get_param( 'action' );

	if ( 'activity_kit' !== get_post_type( $post_id ) ) {
		return new \WP_Error( 'invalid_post', __( 'Invalid activity kit.', 'wporg-learn' ), array( 'status' => 404 ) );
	}

	// Rate-limit: one increment per IP per post per action per 24 hours.
	$rate_key = 'ak_rate_' . md5( $post_id . $action . ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	if ( get_transient( $rate_key ) ) {
		return rest_ensure_response( array( 'tracked' => false, 'reason' => 'rate_limited' ) );
	}
	set_transient( $rate_key, 1, DAY_IN_SECONDS );

	$meta_key = 'view' === $action ? '_view_count' : '_download_count';
	$current  = (int) get_post_meta( $post_id, $meta_key, true );
	update_post_meta( $post_id, $meta_key, $current + 1 );

	log_event( $post_id, $action );

	return rest_ensure_response( array( 'tracked' => true ) );
}

/**
 * Handle GET /activity-kits/v1/stats
 *
 * @param \WP_REST_Request $request
 * @return \WP_REST_Response
 */
function handle_stats( $request ) {
	$metric = $request->get_param( 'metric' );
	$range  = $request->get_param( 'range' );
	$kit    = $request->get_param( 'kit' );

	$kits = get_posts( array(
		'post_type'      => 'activity_kit',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'draft' ),
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$results = array();

	foreach ( $kits as $kit_post ) {
		if ( $kit && $kit_post->post_name !== $kit ) {
			continue;
		}

		$data = array(
			'id'    => $kit_post->ID,
			'title' => $kit_post->post_title,
			'slug'  => $kit_post->post_name,
		);

		if ( 'all' === $range ) {
			if ( 'both' === $metric || 'views' === $metric ) {
				$data['views'] = (int) get_post_meta( $kit_post->ID, '_view_count', true );
			}
			if ( 'both' === $metric || 'downloads' === $metric ) {
				$data['downloads'] = (int) get_post_meta( $kit_post->ID, '_download_count', true );
			}
		} else {
			$data = array_merge( $data, get_stats_from_events( $kit_post->ID, $metric, $range ) );
		}

		$results[] = $data;
	}

	return rest_ensure_response( $results );
}

/**
 * Get stats from the events log table for a given kit, metric, and time range.
 *
 * @param int    $post_id
 * @param string $metric  'both', 'views', or 'downloads'
 * @param string $range   '7d', '30d', or '90d'
 * @return array
 */
function get_stats_from_events( $post_id, $metric, $range ) {
	global $wpdb;

	$table = $wpdb->prefix . 'activity_kit_events';
	$days  = intval( str_replace( 'd', '', $range ) );
	$data  = array();

	if ( 'both' === $metric || 'views' === $metric ) {
		$data['views'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$table}` WHERE post_id = %d AND action = 'view' AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
			$post_id,
			$days
		) );
	}

	if ( 'both' === $metric || 'downloads' === $metric ) {
		$data['downloads'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM `{$table}` WHERE post_id = %d AND action = 'download' AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
			$post_id,
			$days
		) );
	}

	return $data;
}

/**
 * Log a view or download event to the events table.
 *
 * @param int    $post_id
 * @param string $action 'view' or 'download'
 */
function log_event( $post_id, $action ) {
	global $wpdb;

	$table = $wpdb->prefix . 'activity_kit_events';

	$wpdb->insert(
		$table,
		array(
			'post_id'    => $post_id,
			'action'     => $action,
			'created_at' => current_time( 'mysql', true ),
		),
		array( '%d', '%s', '%s' )
	);
}

/**
 * Create the events table if it does not already exist.
 */
function maybe_create_events_table() {
	global $wpdb;

	$table   = $wpdb->prefix . 'activity_kit_events';
	$version = get_option( 'activity_kit_events_db_version', '0' );

	if ( '1.0' === $version ) {
		return;
	}

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint(20) unsigned NOT NULL,
		action varchar(20) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY (id),
		KEY post_id (post_id),
		KEY created_at (created_at)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'activity_kit_events_db_version', '1.0' );
}
