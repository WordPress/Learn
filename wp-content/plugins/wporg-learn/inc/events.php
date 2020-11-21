<?php

namespace WPOrg_Learn\Events;

use DateTime, Exception;
use wpdb;

defined( 'WPINC' ) || die();

/**
 * Retrieve current and upcoming Learn discussion group events using caching mechanisms.
 *
 * @return array
 * @throws Exception
 */
function get_discussion_events() {
	wp_cache_add_global_groups( array( 'learn-events' ) );
	$cache_expiration = HOUR_IN_SECONDS * 12;

	$events = wp_cache_get( 'discussion-events', 'learn-events' );

	if ( false === $events ) {
		$start_date = new DateTime( '@' . strtotime( '-2 hours' ) );
		$end_date = new DateTime( '@' . strtotime( '1 month' ) );

		$raw_events = get_discussion_events_from_db( $start_date, $end_date );

		// Only keep three events for now.
		$raw_events = array_slice( $raw_events, 0, 3 );

		$event_fields_to_keep = array( 'title', 'url', 'description', 'date_utc' );
		$events = array_map(
			function( $event ) use ( $event_fields_to_keep ) {
				return array_intersect_key(
					$event,
					array_fill_keys( $event_fields_to_keep, '' )
				);
			},
			$raw_events
		);

		wp_cache_set( 'discussion-events', $events, 'learn-events', $cache_expiration );
	}

	return $events;
}

/**
 * Retrieve current and upcoming Learn discussion group events directly from the database.
 *
 * This function should probably not be called directly. Use `get_discussion_events()` instead.
 *
 * @global wpdb $wpdb;
 *
 * @param DateTime $start_date
 * @param DateTime $end_date
 *
 * @return array
 */
function get_discussion_events_from_db( DateTime $start_date, DateTime $end_date ) {
	global $wpdb;

	$sql = "
		SELECT *
		FROM wporg_events
		WHERE meetup_url = 'https://www.meetup.com/learn-wordpress-discussions/'
		AND status = 'scheduled'
		AND date_utc BETWEEN %s AND %s
		ORDER BY date_utc
	";

	$results = $wpdb->get_results(
		$wpdb->prepare(
			$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.
			$start_date->format( 'Y-m-d H:i:s' ),
			$end_date->format( 'Y-m-d H:i:s' )
		),
		ARRAY_A
	);

	return $results ?: array();
}
