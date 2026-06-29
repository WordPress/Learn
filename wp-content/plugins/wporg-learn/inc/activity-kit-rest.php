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

	// Build ZIP URL => post ID map for download click matching.
	$zip_url_map = array();
	foreach ( $kits as $kit_post ) {
		$zip_id = (int) get_post_meta( $kit_post->ID, '_activity_zip_id', true );
		if ( $zip_id ) {
			$zip_url = wp_get_attachment_url( $zip_id );
			if ( $zip_url ) {
				$zip_url_map[ $zip_url ] = $kit_post->ID;
			}
		}
	}

	$views_map     = array();
	$downloads_map = array();

	if ( ! $jetpack_unavailable ) {
		if ( 'both' === $metric || 'views' === $metric ) {
			$views_map = get_jetpack_post_views( $range );
		}
		if ( 'both' === $metric || 'downloads' === $metric ) {
			$downloads_map = get_jetpack_download_clicks( $range, $zip_url_map );
		}
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
 * @param string $range One of '7d', '30d', '90d', 'all'.
 * @return array        Map of post_id (int) => view_count (int). Empty on failure.
 */
function get_jetpack_post_views( $range ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) ) {
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

	$result = $stats->get_top_posts(
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

	$top_posts = isset( $result['summary']['top-posts'] ) ? $result['summary']['top-posts'] : array();
	if ( ! is_array( $top_posts ) ) {
		return array();
	}

	$map = array();
	foreach ( $top_posts as $post_data ) {
		if ( isset( $post_data['id'], $post_data['views'] ) ) {
			$map[ (int) $post_data['id'] ] = (int) $post_data['views'];
		}
	}

	return $map;
}

/**
 * Get per-kit download click counts from Jetpack Clicks report.
 *
 * @param string $range       One of '7d', '30d', '90d', 'all'.
 * @param array  $zip_url_map Map of zip_url (string) => post_id (int).
 * @return array              Map of post_id (int) => click_count (int). Empty on failure.
 */
function get_jetpack_download_clicks( $range, $zip_url_map ) {
	if ( ! class_exists( '\Automattic\Jetpack\Stats\WPCOM_Stats' ) || empty( $zip_url_map ) ) {
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

	$map = array();
	foreach ( $clicks as $click ) {
		if ( ! isset( $click['url'], $click['views'] ) ) {
			continue;
		}
		if ( isset( $zip_url_map[ $click['url'] ] ) ) {
			$post_id         = $zip_url_map[ $click['url'] ];
			$map[ $post_id ] = ( isset( $map[ $post_id ] ) ? $map[ $post_id ] : 0 ) + (int) $click['views'];
		}
	}

	return $map;
}
