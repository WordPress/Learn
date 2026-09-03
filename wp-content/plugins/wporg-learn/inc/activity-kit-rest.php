<?php
/**
 * REST API routes for activity kits: stats and download-tracking endpoints.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Activity_Kit_REST;

defined( 'WPINC' ) || die();

/**
 * Most downloads counted per visitor per kit per UTC day. Further downloads are still
 * served, just not counted; see count_download().
 */
const DOWNLOAD_COUNT_CAP_PER_VISITOR = 5;

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
 * For requests that look like a person, counts the download (see
 * count_download()) and then redirects the browser to the actual ZIP file
 * URL. Using a server-side redirect lets us track same-domain downloads that
 * Jetpack's outbound-click tracker misses.
 *
 * @param \WP_REST_Request $request The REST request.
 * @return \WP_REST_Response|\WP_Error 302 redirect on success, WP_Error on failure.
 */
function handle_download( $request ) {
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
		count_download( $kit_post->ID );
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
 * Count one download of a kit for the visitor making the current request.
 *
 * Two daily postmeta buckets per kit, one row per UTC day: '_activity_downloads_YYYYMMDD'
 * counts every download and '_activity_unique_downloads_YYYYMMDD' counts each visitor
 * once. Both are read back by get_download_counts().
 *
 * Visitors are told apart by a one-way hash of the IP address and the kit ID with a salt
 * that changes every UTC day, remembered in a transient that expires at the end of that
 * day. Nothing about the visitor is stored, and the same person hashes to an unrelated
 * value tomorrow. The User-Agent is deliberately left out: it is client-controlled, so
 * hashing it in would let one client mint an unlimited number of "unique" downloaders.
 *
 * The same transient holds how many downloads this visitor has been counted for today.
 * Past DOWNLOAD_COUNT_CAP_PER_VISITOR the file is still served but the counters stop
 * moving, which bounds what a scripted loop can do to the numbers.
 *
 * Two known limits, both acceptable for a "did visitors download" signal: a classroom
 * behind one NAT address counts as one downloader per day, and on a persistent object
 * cache an evicted transient can count a visitor twice. REMOTE_ADDR is used as-is; if
 * the edge does not rewrite it to the client address, confirm the proxy setup with
 * WordPress.org systems before trusting a forwarded header here.
 *
 * @param int $kit_id Post ID of the activity kit.
 */
function count_download( $kit_id ) {
	$day       = gmdate( 'Ymd' );
	$ip        = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$day_salt  = hash_hmac( 'sha256', $day, wp_salt( 'nonce' ) );
	$visitor   = hash_hmac( 'sha256', $ip . '|' . $kit_id, $day_salt );
	$transient = 'ak_dl_' . substr( $visitor, 0, 40 );
	$counted   = (int) get_transient( $transient );

	if ( $counted >= DOWNLOAD_COUNT_CAP_PER_VISITOR ) {
		return;
	}

	// Expire at the next UTC midnight, when the salt changes anyway.
	$seconds_left = DAY_IN_SECONDS - ( time() % DAY_IN_SECONDS );
	set_transient( $transient, $counted + 1, max( MINUTE_IN_SECONDS, $seconds_left ) );

	increment_daily_bucket( $kit_id, '_activity_downloads_' . $day );

	if ( 0 === $counted ) {
		increment_daily_bucket( $kit_id, '_activity_unique_downloads_' . $day );
	}

	// One overwritten row, not history; read by get_last_downloaded_date().
	update_post_meta( $kit_id, '_activity_last_downloaded', gmdate( 'Y-m-d H:i:s' ) );
	wp_cache_delete( $kit_id, 'post_meta' );
}

/**
 * Add one to a kit's daily counter bucket.
 *
 * Seeds the bucket, then increments atomically: update_post_meta()'s compare-and-set
 * is racy when the previous value is 0, and a seed race is harmless because
 * get_download_counts() takes MAX per bucket.
 *
 * @param int    $kit_id   Post ID of the activity kit.
 * @param string $meta_key Bucket key, e.g. '_activity_downloads_20260903'.
 */
function increment_daily_bucket( $kit_id, $meta_key ) {
	global $wpdb;

	add_post_meta( $kit_id, $meta_key, 0, true );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic increment; the caller invalidates the post's meta cache.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
			$kit_id,
			$meta_key
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

	/*
	 * Last-viewed is approximated from Jetpack's own per-post view history, so it shares
	 * $views_map's Jetpack-availability guard, not just the metric filter.
	 */
	$should_fetch_views = ! $jetpack_unavailable && ( 'both' === $metric || 'views' === $metric );

	$views_map       = array();
	$last_viewed_map = array();

	if ( $should_fetch_views ) {
		add_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );
		$views_map = get_jetpack_post_views( $range, $kit_ids );
		remove_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );

		$last_viewed_map = get_last_viewed_dates( $kit_ids );
	}

	$downloads_map          = array();
	$unique_downloaders_map = array();

	if ( 'both' === $metric || 'downloads' === $metric ) {
		$downloads_map          = get_download_counts( $range, $kit_ids );
		$unique_downloaders_map = get_download_counts( $range, $kit_ids, '_activity_unique_downloads_' );
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
				$data['views']       = $views_map[ $kit_post->ID ] ?? 0;
				$data['last_viewed'] = $last_viewed_map[ $kit_post->ID ] ?? null;
			}
		}
		if ( 'both' === $metric || 'downloads' === $metric ) {
			$data['downloads']          = $downloads_map[ $kit_post->ID ] ?? 0;
			$data['unique_downloaders'] = $unique_downloaders_map[ $kit_post->ID ] ?? 0;
			$data['last_downloaded']    = get_last_downloaded_date( $kit_post->ID );
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
 * Get the Unix timestamp boundaries a stats range covers.
 *
 * Shared by both get_download_counts() bucket reads (every download, and one per visitor
 * per day) so the two counts, and any other range-scoped query added later, can't
 * silently drift onto different day spans if this boundary math ever changes.
 *
 * @param string $range One of '7d', '30d', '90d', 'all'.
 * @return array{first: int, last: int} Unix timestamps for the first and last day.
 */
function get_range_timestamps( $range ) {
	$days = get_range_days( $range );
	$now  = time();

	return array(
		'first' => $now - ( $days - 1 ) * DAY_IN_SECONDS,
		'last'  => $now,
	);
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
 * @param string $prefix  Bucket key prefix: '_activity_downloads_' (every download, the
 *                        default) or '_activity_unique_downloads_' (one per visitor per
 *                        day). See count_download().
 * @return array          Map of post_id (int) => count (int).
 */
function get_download_counts( $range, array $kit_ids, $prefix = '_activity_downloads_' ) {
	global $wpdb;

	if ( empty( $kit_ids ) ) {
		return array();
	}

	$bounds    = get_range_timestamps( $range );
	$first_key = $prefix . gmdate( 'Ymd', $bounds['first'] );
	$last_key  = $prefix . gmdate( 'Ymd', $bounds['last'] );

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

/**
 * Get a kit's last-downloaded date, if it has ever been downloaded.
 *
 * Not range-scoped, same as get_the_modified_date() for the 'updated' field — this
 * reflects the kit's real download history regardless of which time range the
 * dashboard happens to be filtered to.
 *
 * @param int $kit_id Post ID of the activity kit.
 * @return string|null 'Y-m-d', or null if the kit has never been downloaded.
 */
function get_last_downloaded_date( $kit_id ) {
	$timestamp = get_post_meta( $kit_id, '_activity_last_downloaded', true );

	if ( ! $timestamp ) {
		return null;
	}

	/*
	 * $timestamp is stored as GMT (current_time( 'mysql', true )) but carries no timezone
	 * of its own — strtotime() would otherwise interpret it in PHP's default timezone,
	 * which can shift the resulting day near midnight. The explicit +0000 forces UTC.
	 */
	return gmdate( 'Y-m-d', strtotime( $timestamp . ' +0000' ) );
}

/**
 * Get, for each kit, the most recent date Jetpack recorded a view for it.
 *
 * Jetpack Stats has no dedicated "last viewed" field, so this is approximated from
 * WPCOM_Stats::get_post_views()'s per-post view history (the 'weeks' field is the one
 * part of that response confirmed to carry day-level granularity). This runs only when
 * an admin loads the stats dashboard — an infrequent, authenticated read, not the
 * per-visitor-page-load case that ruled out custom view tracking for this project.
 *
 * There's no batched form of this specific Jetpack call (unlike get_jetpack_post_views(),
 * which fetches up to 100 kits per request) — get_post_views() is a single-post endpoint,
 * so this is one Jetpack API call per kit on a cache miss. Results, including "no views
 * yet", are kept in a transient for six hours (a real transient, not the object cache:
 * wp_cache_set() only survives the request where a persistent cache is configured). The
 * loop stops at the first Jetpack error, since one failure means the connection is down
 * for every kit and each further call would cost a full timeout. If the kit library grows
 * large enough that even a once-a-day cache-miss burst becomes noticeable, the next step
 * would be warming this cache from a scheduled cron event instead of on demand.
 *
 * @param int[] $kit_ids Post IDs of the activity kits to fetch last-viewed dates for.
 * @return array         Map of post_id (int) => 'Y-m-d' (string). Kits with no cached
 *                        or parseable result are omitted, not set to null.
 */
function get_last_viewed_dates( array $kit_ids ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) || empty( $kit_ids ) ) {
		return array();
	}

	$stats = new \Automattic\Jetpack\Stats\WPCOM_Stats();
	$map   = array();

	foreach ( $kit_ids as $kit_id ) {
		$transient = 'ak_last_viewed_' . $kit_id;
		$cached    = get_transient( $transient );

		if ( false !== $cached ) {
			if ( $cached ) {
				$map[ $kit_id ] = $cached;
			}
			continue;
		}

		$result = $stats->get_post_views( $kit_id, array( 'fields' => 'weeks' ) );

		if ( is_wp_error( $result ) ) {
			break;
		}

		$date = extract_last_viewed_date( $result );

		// Cache the miss too (as an empty string), so a kit with no recorded views doesn't trigger a fresh API call on every dashboard load.
		set_transient( $transient, $date ?? '', 6 * HOUR_IN_SECONDS );

		if ( $date ) {
			$map[ $kit_id ] = $date;
		}
	}

	return $map;
}

/**
 * Pull the most recent date with a nonzero view count out of a get_post_views() response.
 *
 * The WordPress.com /stats/post/{id} response carries `weeks` as a list of weeks, each
 * `array( 'days' => array( array( 'day' => 'Y-m-d', 'count' => n ), ... ), 'total' => ... )`.
 * That list shape is handled first. Two older shapes are accepted as well, a nested
 * 'Y-m-d' => array( 'days' => array( 'Y-m-d' => count ) ) map and a flat 'Y-m-d' => count
 * map. Anything else, including a list index where a date was expected, is ignored, so
 * the result is a real date or null, never an integer that only looks like one.
 *
 * @param array $result Response from WPCOM_Stats::get_post_views().
 * @return string|null 'Y-m-d', or null if no parseable, nonzero-view date was found.
 */
function extract_last_viewed_date( $result ) {
	if ( ! is_array( $result ) || empty( $result['weeks'] ) || ! is_array( $result['weeks'] ) ) {
		return null;
	}

	$last_viewed = null;

	foreach ( $result['weeks'] as $week_key => $week ) {
		if ( is_array( $week ) && isset( $week['days'] ) && is_array( $week['days'] ) ) {
			foreach ( $week['days'] as $day_key => $day ) {
				if ( is_array( $day ) && isset( $day['day'], $day['count'] ) ) {
					// Live shape: a list of { day, count } objects.
					$last_viewed = later_viewed_day( $last_viewed, $day['day'], $day['count'] );
				} elseif ( is_string( $day_key ) ) {
					// Nested map shape: 'Y-m-d' => count.
					$last_viewed = later_viewed_day( $last_viewed, $day_key, $day );
				}
			}
			continue;
		}

		// Flat map shape: 'Y-m-d' => count.
		if ( is_string( $week_key ) && is_scalar( $week ) ) {
			$last_viewed = later_viewed_day( $last_viewed, $week_key, $week );
		}
	}

	return $last_viewed;
}

/**
 * Keep whichever of two dates is later, if the candidate is a real Y-m-d with views.
 *
 * @param string|null $current The latest viewed day found so far.
 * @param mixed       $day     Candidate day; anything but a 'Y-m-d' string is ignored.
 * @param mixed       $count   View count for that day.
 * @return string|null
 */
function later_viewed_day( $current, $day, $count ) {
	if ( ! is_string( $day ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day ) || (int) $count <= 0 ) {
		return $current;
	}

	return ( null === $current || $day > $current ) ? $day : $current;
}
