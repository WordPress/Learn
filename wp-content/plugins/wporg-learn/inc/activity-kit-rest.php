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
 * Increments the kit's download counter (stored in post meta) for requests that
 * look like a person, and redirects the browser to the actual ZIP file URL.
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
	$user_agent = sanitize_text_field( wp_unslash( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' ) );
	$is_bot     = empty( $user_agent ) || preg_match( '/bot|crawl|slurp|spider|mediapartners|facebookexternalhit|linkedinbot|twitterbot|whatsapp|slack|discord|prefetch/i', $user_agent );

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
		 * Direct UPDATE, not update_post_meta(): its $prev_value CAS drops the WHERE
		 * clause when the previous value is 0, so two concurrent first-downloads
		 * would both write 1.
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic increment; cache invalidated immediately below.
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
				$kit_post->ID,
				'_activity_download_count'
			)
		);
		if ( 0 === $updated ) {
			// No row yet — insert with an initial count of 1.
			// $wpdb->query() returns false on DB error and 0 when no rows matched;
			// strict comparison avoids falling into this branch on a real error.
			add_post_meta( $kit_post->ID, '_activity_download_count', 1, true );
		}
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
	 * Map the UI range to one or more 30-day windows. Each window is defined by
	 * how many days back its end-date is offset from today. 'all' uses 6 windows
	 * (≈ 6 months) rather than 3, giving a meaningful distinction from '90d'.
	 * Extending further would multiply sequential API calls proportionally; 6 is
	 * a reasonable ceiling for an admin-only dashboard with a small post count.
	 */
	switch ( $range ) {
		case '7d':
			$windows = array(
				array(
					'num' => 7, 'offset' => 0,
				),
			);
			break;
		case '30d':
			$windows = array(
				array(
					'num' => 30, 'offset' => 0,
				),
			);
			break;
		case '90d':
			$windows = array(
				array(
					'num' => 30, 'offset' => 0,
				),
				array(
					'num' => 30, 'offset' => 30,
				),
				array(
					'num' => 30, 'offset' => 60,
				),
			);
			break;
		case 'all':
		default:
			$windows = array(
				array(
					'num' => 30, 'offset' => 0,
				),
				array(
					'num' => 30, 'offset' => 30,
				),
				array(
					'num' => 30, 'offset' => 60,
				),
				array(
					'num' => 30, 'offset' => 90,
				),
				array(
					'num' => 30, 'offset' => 120,
				),
				array(
					'num' => 30, 'offset' => 150,
				),
			);
			break;
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

			if ( is_wp_error( $result ) ) {
				// Real API failure — return empty so the caller shows 0 for all
				// kits (a visible failure signal) rather than a plausible-looking
				// undercount that is harder to detect.
				return array();
			}
			if ( ! is_array( $result ) ) {
				continue;
			}

			$post_views = isset( $result['posts'] ) ? $result['posts'] : array();
			if ( ! is_array( $post_views ) ) {
				continue;
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
