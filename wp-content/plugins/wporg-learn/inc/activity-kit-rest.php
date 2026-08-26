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

	/*
	 * Route on post ID (integer) rather than slug so that kits with underscores,
	 * percent-encoded non-Latin characters, or other slug forms not matched by a
	 * narrow character class all resolve correctly.
	 */
	register_rest_route(
		'activity-kits/v1',
		'/download/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\handle_download',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * Get the tracked download URL for an activity kit.
 *
 * @param int $kit_id Post ID of the activity kit.
 * @return string     URL of the counting download endpoint.
 */
function get_download_url( $kit_id ) {
	return rest_url( 'activity-kits/v1/download/' . absint( $kit_id ) );
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
 * Handle GET /activity-kits/v1/download/{id}
 *
 * Increments the kit's download counter (stored in one post meta row per UTC
 * day) for requests that look like a person, and redirects the browser to the
 * actual ZIP file URL.
 * Using a server-side redirect lets us track same-domain downloads that
 * Jetpack's outbound-click tracker misses.
 *
 * @param \WP_REST_Request $request The REST request.
 * @return \WP_REST_Response|\WP_Error 302 redirect on success, WP_Error on failure.
 */
function handle_download( $request ) {
	global $wpdb;

	/*
	 * Skip counting unfurlers, scanners and prefetches. Still redirect: the
	 * heuristic has false positives, and those should cost an uncounted
	 * download, not a failed one.
	 */
	$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	// Browsers signal speculative loads via headers, not the User-Agent: Sec-Purpose (standard), Purpose (WebKit), X-moz (older Firefox).
	$purpose = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? $_SERVER['HTTP_X_MOZ'] ?? '' ) );
	$is_bot  = empty( $user_agent )
		|| preg_match( '/bot|crawl|slurp|spider|mediapartners|facebookexternalhit|linkedinbot|twitterbot|whatsapp|slack|discord|prefetch/i', $user_agent )
		|| preg_match( '/prefetch|prerender|preview/i', $purpose );

	$kit_id   = absint( $request->get_param( 'id' ) );
	$kit_post = get_post( $kit_id );

	if ( ! $kit_post || 'activity_kit' !== $kit_post->post_type || 'publish' !== $kit_post->post_status ) {
		return new \WP_Error( 'activity_kit_not_found', __( 'Activity kit not found.', 'wporg-learn' ), array( 'status' => 404 ) );
	}

	$zip_id = (int) get_post_meta( $kit_post->ID, '_activity_zip_id', true );

	if ( ! $zip_id ) {
		return new \WP_Error( 'activity_kit_no_zip', __( 'No downloadable file attached to this activity kit.', 'wporg-learn' ), array( 'status' => 404 ) );
	}

	$zip_url = wp_get_attachment_url( $zip_id );

	if ( ! $zip_url ) {
		return new \WP_Error( 'activity_kit_zip_url', __( 'Could not resolve the download URL.', 'wporg-learn' ), array( 'status' => 500 ) );
	}

	if ( ! $is_bot ) {
		/*
		 * Seed today's bucket, then increment atomically — update_post_meta()'s
		 * CAS is racy when the previous value is 0, and a seed race is harmless
		 * because reads take MAX per bucket.
		 */
		$meta_key = '_activity_downloads_' . gmdate( 'Ymd' );
		add_post_meta( $kit_post->ID, $meta_key, 0, true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic increment; cache invalidated immediately below.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
				$kit_post->ID,
				$meta_key
			)
		);
		wp_cache_delete( $kit_post->ID, 'post_meta' );
	}

	return new \WP_REST_Response(
		null,
		302,
		array(
			'Location'      => esc_url_raw( $zip_url ),
			'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
			'Pragma'        => 'no-cache',
		)
	);
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

	// Narrow to only the requested kit when a slug filter is active, so that
	// the Jetpack API is not queried for IDs whose data will never be returned.
	$kit_ids = array();
	foreach ( $kits as $kit_post ) {
		if ( ! $kit || $kit_post->post_name === $kit ) {
			$kit_ids[] = $kit_post->ID;
		}
	}

	$views_map = array();

	if ( ! $jetpack_unavailable && ( 'both' === $metric || 'views' === $metric ) ) {
		add_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );
		$views_map = get_jetpack_post_views( $range, $kit_ids );
		remove_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );
	}

	$downloads_map = array();

	if ( 'both' === $metric || 'downloads' === $metric ) {
		$downloads_map = get_download_counts( $range, $kit_ids );
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
			$data['downloads'] = $downloads_map[ $kit_post->ID ] ?? 0;
		}

		$results[] = $data;
	}

	return rest_ensure_response( $results );
}

/**
 * Get the day span a stats range covers.
 *
 * Shared by get_jetpack_post_views() and get_download_counts() so views and
 * downloads always cover the same period.
 *
 * @param string $range One of '7d', '30d', '90d', 'all'.
 * @return int          Number of days the range covers.
 */
function get_range_days( $range ) {
	switch ( $range ) {
		case '7d':
			return 7;
		case '30d':
			return 30;
		case '90d':
			return 90;
		default:
			return 180;
	}
}

/**
 * Get per-post view counts from Jetpack Stats for a given time range.
 *
 * Uses get_total_post_views() to query specific post IDs directly, rather than
 * get_top_posts() which only returns the site-wide top-N posts by all-time views
 * and misses recently published kits with low overall traffic.
 *
 * API constraints (Jetpack 13.3.1 / WPCOM /stats/views/posts endpoint):
 *   - `period` is ignored; only daily granularity is returned.
 *   - `num` is capped at 30 days per call.
 *   - `post_ids` accepts at most 100 IDs per call.
 *
 * To cover ranges longer than 30 days, multiple 30-day windows are issued with
 * a date offset and the results are summed. post_ids are chunked into groups of
 * 100 so the library can grow past 100 kits without silently losing data.
 *
 * @param string $range   One of '7d', '30d', '90d', 'all'.
 * @param int[]  $kit_ids Post IDs of the activity kits to fetch views for.
 * @return array          Map of post_id (int) => view_count (int). Empty on failure.
 */
function get_jetpack_post_views( $range, array $kit_ids ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) || empty( $kit_ids ) ) {
		return array();
	}

	$stats = new \Automattic\Jetpack\Stats\WPCOM_Stats();

	/*
	 * Break the range's day span into windows of at most 30 days — the WPCOM
	 * /stats/views/posts API caps `num` at 30 per call. Each window is defined
	 * by how many days back its end-date is offset from today.
	 */
	$days    = get_range_days( $range );
	$windows = array();
	for ( $offset = 0; $offset < $days; $offset += 30 ) {
		$windows[] = array(
			'num'    => min( 30, $days - $offset ),
			'offset' => $offset,
		);
	}

	// Pre-compute window end-dates once so every chunk uses the same calendar
	// day, even if a UTC midnight falls between chunk iterations.
	$now           = time();
	$dated_windows = array();
	foreach ( $windows as $window ) {
		$dated_windows[] = array(
			'num'  => $window['num'],
			'date' => gmdate( 'Y-m-d', $now - $window['offset'] * DAY_IN_SECONDS ),
		);
	}

	$chunks = array_chunk( $kit_ids, 100 );
	$map    = array();

	foreach ( $chunks as $chunk ) {
		$post_ids_str = implode( ',', array_map( 'absint', $chunk ) );

		foreach ( $dated_windows as $window ) {
			$result = $stats->get_total_post_views(
				array(
					'post_ids' => $post_ids_str,
					'num'      => $window['num'],
					'date'     => $window['date'],
				)
			);

			// Any unusable response returns empty so all kits show 0 (a visible failure) rather than a plausible-looking undercount.
			if ( is_wp_error( $result ) || ! is_array( $result ) ) {
				return array();
			}

			$post_views = isset( $result['posts'] ) ? $result['posts'] : array();
			if ( ! is_array( $post_views ) ) {
				return array();
			}

			foreach ( $post_views as $post_data ) {
				// The views/posts API uses uppercase 'ID' (unlike top-posts which uses 'id').
				if ( isset( $post_data['ID'], $post_data['views'] ) ) {
					$id         = (int) $post_data['ID'];
					$map[ $id ] = ( isset( $map[ $id ] ) ? $map[ $id ] : 0 ) + (int) $post_data['views'];
				}
			}
		}
	}

	return $map;
}

/**
 * Get per-kit download counts from the daily download meta buckets.
 *
 * Downloads are stored as one meta row per kit per UTC day
 * ('_activity_downloads_YYYYMMDD', see handle_download()), so they can be
 * summed over the same day span as the Jetpack view windows and the download
 * rate always divides two figures covering the identical period.
 *
 * MAX() per bucket (instead of SUM) makes duplicate rows from a concurrent
 * first-download race harmless: the atomic UPDATE in handle_download()
 * increments every duplicate equally, so each row holds the full count for
 * its day.
 *
 * @param string $range   One of '7d', '30d', '90d', 'all'.
 * @param int[]  $kit_ids Post IDs of the activity kits to fetch downloads for.
 * @return array          Map of post_id (int) => download_count (int).
 */
function get_download_counts( $range, array $kit_ids ) {
	global $wpdb;

	if ( empty( $kit_ids ) ) {
		return array();
	}

	$days      = get_range_days( $range );
	$now       = time();
	$first_key = '_activity_downloads_' . gmdate( 'Ymd', $now - ( $days - 1 ) * DAY_IN_SECONDS );
	$last_key  = '_activity_downloads_' . gmdate( 'Ymd', $now );

	$id_placeholders = implode( ',', array_fill( 0, count( $kit_ids ), '%d' ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The meta API has no ranged multi-key read.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $id_placeholders is a list of %d placeholders built above from count().
			"SELECT post_id, meta_key, MAX( CAST( meta_value AS UNSIGNED ) ) AS downloads FROM {$wpdb->postmeta} WHERE post_id IN ( {$id_placeholders} ) AND meta_key BETWEEN %s AND %s GROUP BY post_id, meta_key",
			array_merge( $kit_ids, array( $first_key, $last_key ) )
		)
	);

	$map = array();
	foreach ( $rows as $row ) {
		$id         = (int) $row->post_id;
		$map[ $id ] = ( isset( $map[ $id ] ) ? $map[ $id ] : 0 ) + (int) $row->downloads;
	}

	return $map;
}
