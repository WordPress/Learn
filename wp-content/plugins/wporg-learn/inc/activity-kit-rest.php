<?php
/**
 * REST API routes for activity kits: stats and download-tracking endpoints.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Activity_Kit_REST;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'rest_api_init', __NAMESPACE__ . '\register_routes' );

/**
 * Register REST API routes for activity kits.
 */
function register_routes() {
	register_rest_route(
		'activity-kits/v1',
		'/stats',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\handle_stats',
			'permission_callback' => function () {
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

	register_rest_route(
		'activity-kits/v1',
		'/download/(?P<slug>[a-z0-9-]+)',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\handle_download',
			'permission_callback' => '__return_true',
			'args'                => array(
				'slug' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
				),
			),
		)
	);
}

/**
 * Shorten Jetpack's stats API cache so the activity kit dashboard tracks
 * WordPress.com's near-real-time counts more closely. Registered only for the
 * duration of the activity kit stats REST request (see handle_stats()), so it
 * does not affect other Jetpack Stats consumers site-wide.
 *
 * Jetpack caches stats REST responses for 5 minutes by default. This caps the
 * lifetime at 1 minute (never lengthening it) and floors it at 1 second, so a
 * stray 0 from another filter can't turn into a never-expiring transient. Note
 * this only affects the local cache layer — WordPress.com's own per-post
 * aggregation has its own, separate processing delay that cannot be shortened
 * from here.
 *
 * @param int $expiration Default cache expiration, in seconds.
 * @return int Cache expiration, in seconds.
 */
function stats_cache_expiration( $expiration ) {
	return (int) max( 1, min( $expiration, MINUTE_IN_SECONDS ) );
}

/**
 * Handle GET /activity-kits/v1/download/{slug}
 *
 * Increments the kit's download counter (stored in post meta) and redirects
 * the browser to the actual ZIP file URL. Using a server-side redirect lets
 * us track same-domain downloads that Jetpack's outbound-click tracker misses.
 *
 * @param \WP_REST_Request $request The REST request.
 * @return \WP_REST_Response|\WP_Error 302 redirect on success, WP_Error on failure.
 */
function handle_download( $request ) {
	$slug = $request->get_param( 'slug' );

	$kits = get_posts(
		array(
			'post_type'      => 'activity_kit',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'name'           => $slug,
		)
	);

	if ( empty( $kits ) ) {
		return new \WP_Error( 'activity_kit_not_found', __( 'Activity kit not found.', 'wporg-learn' ), array( 'status' => 404 ) );
	}

	$kit_post = $kits[0];
	$zip_id   = (int) get_post_meta( $kit_post->ID, '_activity_zip_id', true );

	if ( ! $zip_id ) {
		return new \WP_Error( 'activity_kit_no_zip', __( 'No downloadable file attached to this activity kit.', 'wporg-learn' ), array( 'status' => 404 ) );
	}

	$zip_url = wp_get_attachment_url( $zip_id );

	if ( ! $zip_url ) {
		return new \WP_Error( 'activity_kit_zip_url', __( 'Could not resolve the download URL.', 'wporg-learn' ), array( 'status' => 500 ) );
	}

	// Increment the download counter stored in post meta.
	// Use a compare-and-swap retry loop (update_post_meta's $prev_value arg) to avoid
	// lost increments when two requests arrive simultaneously.
	$retries = 0;
	do {
		$current_count = (int) get_post_meta( $kit_post->ID, '_activity_download_count', true );
		$updated       = update_post_meta( $kit_post->ID, '_activity_download_count', $current_count + 1, $current_count );
		$retries++;
	} while ( ! $updated && $retries < 5 );

	return new \WP_REST_Response( null, 302, array( 'Location' => esc_url_raw( $zip_url ) ) );
}

/**
 * Handle GET /activity-kits/v1/stats
 *
 * @param \WP_REST_Request $request The REST request.
 * @return \WP_REST_Response
 */
function handle_stats( $request ) {
	$metric = $request->get_param( 'metric' );
	$range  = $request->get_param( 'range' );
	$kit    = $request->get_param( 'kit' );

	$kits = get_posts(
		array(
			'post_type'      => 'activity_kit',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$jetpack_unavailable = ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' );

	$kit_ids   = wp_list_pluck( $kits, 'ID' );
	$views_map = array();

	if ( ! $jetpack_unavailable && ( 'both' === $metric || 'views' === $metric ) ) {
		add_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );
		$views_map = get_jetpack_post_views( $range, $kit_ids );
		remove_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );
	}

	$results = array();

	foreach ( $kits as $kit_post ) {
		if ( $kit && $kit_post->post_name !== $kit ) {
			continue;
		}

		$data = array(
			'id'      => $kit_post->ID,
			'title'   => $kit_post->post_title,
			'slug'    => $kit_post->post_name,
			'updated' => get_the_modified_date( 'Y-m-d', $kit_post->ID ),
		);

		// Views depend on Jetpack; downloads come from post meta regardless.
		if ( 'both' === $metric || 'views' === $metric ) {
			if ( $jetpack_unavailable ) {
				$data['jetpack_unavailable'] = true;
				$data['views']               = 0;
			} else {
				$data['views'] = $views_map[ $kit_post->ID ] ?? 0;
			}
		}
		if ( 'both' === $metric || 'downloads' === $metric ) {
			$data['downloads'] = (int) get_post_meta( $kit_post->ID, '_activity_download_count', true );
		}

		$results[] = $data;
	}

	return rest_ensure_response( $results );
}

/**
 * Get per-post view counts from Jetpack Stats for a given time range.
 *
 * Uses get_total_post_views() to query specific post IDs directly, rather
 * than get_top_posts() which only returns site-wide top posts and misses
 * recently published kits with low overall traffic.
 *
 * @param string $range    One of '7d', '30d', '90d', 'all'.
 * @param array  $kit_ids  Array of post IDs to query.
 * @return array           Map of post_id (int) => view_count (int). Empty on failure.
 */
function get_jetpack_post_views( $range, array $kit_ids ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) || empty( $kit_ids ) ) {
		return array();
	}

	$stats = new \Automattic\Jetpack\Stats\WPCOM_Stats();

	if ( 'all' === $range ) {
		$period = 'month';
		$num    = 36;
	} else {
		$period = 'day';
		$num    = intval( str_replace( 'd', '', $range ) );
	}

	$result = $stats->get_total_post_views(
		array(
			'post_ids' => implode( ',', array_map( 'absint', $kit_ids ) ),
			'period'   => $period,
			'num'      => $num,
			'date'     => gmdate( 'Y-m-d' ),
		)
	);

	if ( is_wp_error( $result ) || ! is_array( $result ) ) {
		return array();
	}

	$post_views = isset( $result['posts'] ) ? $result['posts'] : array();
	if ( ! is_array( $post_views ) ) {
		return array();
	}

	$map = array();
	foreach ( $post_views as $post_data ) {
		if ( isset( $post_data['ID'], $post_data['views'] ) ) {
			$map[ (int) $post_data['ID'] ] = (int) $post_data['views'];
		}
	}

	return $map;
}
