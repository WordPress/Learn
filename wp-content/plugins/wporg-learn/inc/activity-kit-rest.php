<?php
/**
 * REST API routes for activity kits: stats endpoint backed by Jetpack Stats.
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

	// Build ZIP URL => post IDs map for download click matching.
	// A URL maps to an array of kit IDs so that if multiple kits share a zip,
	// each kit is credited rather than silently dropped by a key collision.
	$zip_url_map = array();
	foreach ( $kits as $kit_post ) {
		$zip_id = (int) get_post_meta( $kit_post->ID, '_activity_zip_id', true );
		if ( $zip_id ) {
			$zip_url = wp_get_attachment_url( $zip_id );
			if ( $zip_url ) {
				$zip_url_map[ $zip_url ][] = $kit_post->ID;
			}
		}
	}

	$views_map     = array();
	$downloads_map = array();

	if ( ! $jetpack_unavailable ) {
		add_filter( 'jetpack_fetch_stats_cache_expiration', __NAMESPACE__ . '\stats_cache_expiration' );

		if ( 'both' === $metric || 'views' === $metric ) {
			$kit_ids   = wp_list_pluck( $kits, 'ID' );
			$views_map = get_jetpack_post_views( $range, $kit_ids );
		}
		if ( 'both' === $metric || 'downloads' === $metric ) {
			$downloads_map = get_jetpack_download_clicks( $range, $zip_url_map );
		}

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

		if ( $jetpack_unavailable ) {
			$data['jetpack_unavailable'] = true;
			$data['views']               = 0;
			$data['downloads']           = 0;
		} else {
			if ( 'both' === $metric || 'views' === $metric ) {
				$data['views'] = $views_map[ $kit_post->ID ] ?? 0;
			}
			if ( 'both' === $metric || 'downloads' === $metric ) {
				$data['downloads'] = $downloads_map[ $kit_post->ID ] ?? 0;
			}
		}

		$results[] = $data;
	}

	return rest_ensure_response( $results );
}

/**
 * Get per-post view counts from Jetpack Stats for a given time range.
 *
 * Uses get_total_post_views() rather than get_top_posts() so that views are
 * fetched by post ID directly. get_top_posts() only returns the site-wide top N
 * posts ranked by all-time views, which means newly published activity kits
 * never appear — they're outranked by years of established content.
 *
 * API constraints (Jetpack / WPCOM /stats/views/posts endpoint):
 *   - `period` is silently discarded; only daily granularity is returned.
 *   - `num` is capped at 30 days per call (422 if larger).
 *   - `post_ids` accepts at most 100 IDs per call.
 *
 * To cover ranges longer than 30 days, multiple 30-day windows are issued with
 * a date offset and the results are summed. Kit IDs are chunked into groups of
 * 100 so the library can grow past 100 kits without silently losing data.
 * '90d' uses 3 windows (90 days); 'all' uses 6 windows (≈ 180 days) to give a
 * meaningful distinction from the 90-day range.
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
					'num'    => 7,
					'offset' => 0,
				),
			);
			break;
		case '30d':
			$windows = array(
				array(
					'num'    => 30,
					'offset' => 0,
				),
			);
			break;
		case '90d':
			$windows = array(
				array(
					'num'    => 30,
					'offset' => 0,
				),
				array(
					'num'    => 30,
					'offset' => 30,
				),
				array(
					'num'    => 30,
					'offset' => 60,
				),
			);
			break;
		case 'all':
		default:
			$windows = array(
				array(
					'num'    => 30,
					'offset' => 0,
				),
				array(
					'num'    => 30,
					'offset' => 30,
				),
				array(
					'num'    => 30,
					'offset' => 60,
				),
				array(
					'num'    => 30,
					'offset' => 90,
				),
				array(
					'num'    => 30,
					'offset' => 120,
				),
				array(
					'num'    => 30,
					'offset' => 150,
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

			/*
			 * Any unusable response — API failure or unexpected shape — returns empty
			 * so the caller shows 0 for all kits (a visible failure signal) rather
			 * than a plausible-looking undercount. Skipping just the bad window would
			 * drop its views from a sum the other windows still make look reasonable.
			 */
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
 * Get per-kit download click counts from Jetpack Clicks report.
 *
 * Clicks are scoped to the same window as get_jetpack_post_views() so that the
 * download rate (downloads / views) divides two figures covering the same span.
 * For 'all' that means exactly 180 days (period=day, num=180), matching the
 * 6 × 30-day view windows — calendar-month boundaries would give 181–184 days
 * and introduce a slight rate inflation relative to the view window.
 *
 * @param string $range       One of '7d', '30d', '90d', 'all'.
 * @param array  $zip_url_map Map of zip_url (string) => array of post_ids (int[]).
 * @return array              Map of post_id (int) => click_count (int). Empty on failure.
 */
function get_jetpack_download_clicks( $range, $zip_url_map ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) || empty( $zip_url_map ) ) {
		return array();
	}

	$stats = new \Automattic\Jetpack\Stats\WPCOM_Stats();

	if ( 'all' === $range ) {
		// Use day granularity for 'all' so the window is exactly 180 days,
		// matching the 6 × 30-day view windows in get_jetpack_post_views().
		// period=month would give 181–184 days depending on the calendar.
		$period = 'day';
		$num    = 180;
	} else {
		$period = 'day';
		$num    = intval( str_replace( 'd', '', $range ) );
	}

	$result = $stats->get_clicks(
		array(
			'period'    => $period,
			'num'       => $num,
			'date'      => gmdate( 'Y-m-d' ),
			'summarize' => true,
			'max'       => 1000,
		)
	);

	if ( is_wp_error( $result ) || ! is_array( $result ) ) {
		return array();
	}

	$clicks = isset( $result['summary']['clicks'] ) ? $result['summary']['clicks'] : array();
	if ( ! is_array( $clicks ) ) {
		return array();
	}

	// Jetpack groups clicks into a tree: a domain node carries a NULL url and
	// the real per-URL clicks under `children`, while a lone click can appear
	// as a flat leaf. Walk the tree and keep only leaves with a url + views.
	$leaves = array();
	$stack  = $clicks;
	while ( $stack ) {
		$node = array_pop( $stack );
		if ( ! empty( $node['children'] ) && is_array( $node['children'] ) ) {
			foreach ( $node['children'] as $child ) {
				$stack[] = $child;
			}
		} elseif ( isset( $node['url'], $node['views'] ) ) {
			$leaves[] = $node;
		}
	}

	$map = array();
	foreach ( $leaves as $leaf ) {
		if ( ! isset( $zip_url_map[ $leaf['url'] ] ) ) {
			continue;
		}
		foreach ( $zip_url_map[ $leaf['url'] ] as $post_id ) {
			$map[ $post_id ] = ( isset( $map[ $post_id ] ) ? $map[ $post_id ] : 0 ) + (int) $leaf['views'];
		}
	}

	return $map;
}
