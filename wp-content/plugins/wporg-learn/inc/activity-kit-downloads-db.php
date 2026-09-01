<?php
/**
 * Unique-downloader tracking for activity kits.
 *
 * Jetpack Stats has no per-post unique-visitor data (only site-wide aggregates), and a
 * per-page-view tracking table isn't viable here — a similar "record on every page load"
 * endpoint was previously rejected for this project on infra/caching grounds, not privacy
 * grounds. Downloads are a different story: they're already tracked server-side, on every
 * download (a comparatively rare event), via the daily postmeta buckets in
 * activity-kit-rest.php. This file adds one more fact to that same low-frequency write: a
 * de-duplicated (kit, visitor, day) record, so a distinct-downloader count can be read back
 * without needing per-post visitor data from Jetpack.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Activity_Kit_Downloads_DB;

defined( 'WPINC' ) || die();

const DB_VERSION        = '1.0';
const DB_VERSION_OPTION = 'activity_kit_downloads_db_version';
const SECRET_OPTION     = 'activity_kit_downloads_secret';

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\maybe_upgrade_schema' );

/**
 * Get the unique-downloads table's full name, including the site's table prefix.
 *
 * @return string
 */
function get_table_name() {
	global $wpdb;
	return "{$wpdb->prefix}activity_kit_downloads";
}

/**
 * Create or upgrade the unique-downloads table, if the stored schema version is behind.
 *
 * Hooked to init (not admin_init) so the table exists before the very first public,
 * unauthenticated download request tries to write to it — on a fresh install, an
 * admin_init-only hook would leave a real front-end visitor's download silently
 * uncounted (no error, just a write against a table that doesn't exist yet) if they
 * arrive before any admin has ever loaded a wp-admin page.
 *
 * Calling dbDelta() itself is cheap to skip (only the get_option() check runs on every
 * request — already covered by the same alloptions cache WordPress loads regardless),
 * but the CREATE/ALTER it would run on a real change is not, hence the version gate. On
 * WordPress.org's own infrastructure this may need the table provisioned separately
 * rather than relying on dbDelta() in production — see the PR description.
 */
function maybe_upgrade_schema() {
	if ( get_option( DB_VERSION_OPTION ) === DB_VERSION ) {
		return;
	}

	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name      = get_table_name();
	$charset_collate = $wpdb->get_charset_collate();

	/*
	 * kit_id, day, visitor_hash (not visitor-first) so the unique index's leading
	 * columns also serve the "WHERE kit_id IN (...) AND day BETWEEN ..." read query
	 * in get_unique_downloader_counts(), not just the write-side de-duplication.
	 */
	$sql = "CREATE TABLE {$table_name} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		kit_id BIGINT UNSIGNED NOT NULL,
		visitor_hash CHAR(64) NOT NULL,
		day DATE NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY kit_day_visitor (kit_id, day, visitor_hash),
		KEY day (day)
	) {$charset_collate};";

	dbDelta( $sql );

	/*
	 * Only record the upgrade as done once the table is confirmed to exist — dbDelta()
	 * doesn't reliably signal failure, and marking the version as current after a failed
	 * CREATE (a permissions issue, a syntax problem introduced by a future edit, etc.)
	 * would stop every later request from retrying, leaving download tracking silently
	 * and permanently broken on that environment.
	 */
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-off existence check right after a schema change; not a data query.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
		update_option( DB_VERSION_OPTION, DB_VERSION );
	}
}

/**
 * Get the site's secret for hashing visitor identifiers, generating one on first use.
 *
 * Never exposed outside this file. Not autoloaded, since it's read only when a
 * download happens, not on every page load.
 *
 * @return string
 */
function get_site_secret() {
	$secret = get_option( SECRET_OPTION );

	if ( ! $secret ) {
		$generated = wp_generate_password( 64, true, true );

		/*
		 * add_option() is a no-op if another concurrent request already won this race
		 * and inserted the option first — re-read rather than trusting $generated, so
		 * every request ends up hashing against the one secret that actually persisted.
		 */
		if ( add_option( SECRET_OPTION, $generated, '', false ) ) {
			$secret = $generated;
		} else {
			$secret = get_option( SECRET_OPTION );
		}
	}

	return $secret;
}

/**
 * Get a salt scoped to one UTC day.
 *
 * This is an HMAC of the day string, not a value stored and rotated on a schedule —
 * there's nothing to fail to rotate. Because HMAC is a pseudorandom function, knowing
 * one day's salt reveals nothing about any other day's salt without also knowing the
 * site secret, which never leaves this function. That's what makes visitor hashes
 * un-correlatable across two different days: the same visitor downloading the same kit
 * on two different days gets two unrelated hashes, so no persistent per-visitor
 * identifier is ever stored or derivable.
 *
 * @param string|null $day Date in 'Ymd' format. Defaults to today (UTC).
 * @return string
 */
function get_daily_salt( $day = null ) {
	$day = $day ?? gmdate( 'Ymd' );
	return hash_hmac( 'sha256', $day, get_site_secret() );
}

/**
 * Get a one-way hash identifying a visitor for one kit on one day.
 *
 * No raw IP address is ever stored — only this hash. Combined with the daily salt,
 * the same visitor produces an unrelated hash on a different day (see get_daily_salt()).
 *
 * @param int         $kit_id     Post ID of the activity kit.
 * @param string      $ip         Visitor's IP address.
 * @param string      $user_agent Visitor's User-Agent string.
 * @param string|null $day        Date in 'Ymd' format. Defaults to today (UTC).
 * @return string
 */
function get_visitor_hash( $kit_id, $ip, $user_agent, $day = null ) {
	return hash( 'sha256', get_daily_salt( $day ) . '|' . $ip . '|' . $user_agent . '|' . $kit_id );
}

/**
 * Record one download for a (kit, visitor, day) combination.
 *
 * A no-op if this exact combination was already recorded today, by design — this is
 * what makes the count that reads it back a distinct-downloader count rather than a
 * raw download count (that's still tracked separately, unchanged, via the daily
 * postmeta buckets in activity-kit-rest.php).
 *
 * @param int    $kit_id     Post ID of the activity kit.
 * @param string $ip         Visitor's IP address.
 * @param string $user_agent Visitor's User-Agent string.
 */
function record_download( $kit_id, $ip, $user_agent ) {
	global $wpdb;

	$visitor_hash = get_visitor_hash( $kit_id, $ip, $user_agent );
	$table_name   = get_table_name();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No wpdb API for INSERT IGNORE; a duplicate is an expected, harmless no-op.
	$result = $wpdb->query(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table_name is built from $wpdb->prefix, not user input.
			"INSERT IGNORE INTO {$table_name} ( kit_id, visitor_hash, day ) VALUES ( %d, %s, %s )",
			$kit_id,
			$visitor_hash,
			gmdate( 'Y-m-d' )
		)
	);

	// A missing table (e.g. schema not yet created on this environment) fails the
	// query silently otherwise — surface it so a broken deploy is visible somewhere.
	if ( false === $result && $wpdb->last_error ) {
		error_log( 'wporg-learn: activity kit download tracking failed: ' . $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Best-effort visibility for a silent write failure; no logging framework exists in this plugin to use instead.
	}
}

/**
 * Get per-kit unique-downloader counts for a given time range.
 *
 * @param string $range   One of '7d', '30d', '90d', 'all'.
 * @param int[]  $kit_ids Post IDs of the activity kits to fetch counts for.
 * @return array          Map of post_id (int) => unique_downloader_count (int).
 */
function get_unique_downloader_counts( $range, array $kit_ids ) {
	global $wpdb;

	if ( empty( $kit_ids ) ) {
		return array();
	}

	$bounds     = \WPOrg_Learn\Activity_Kit_REST\get_range_timestamps( $range );
	$first_day  = gmdate( 'Y-m-d', $bounds['first'] );
	$last_day   = gmdate( 'Y-m-d', $bounds['last'] );
	$table_name = get_table_name();

	$id_placeholders = implode( ',', array_fill( 0, count( $kit_ids ), '%d' ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No wpdb API for COUNT(DISTINCT ...) over a custom table.
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $id_placeholders is a list of %d placeholders built above from count().
			"SELECT kit_id, COUNT( DISTINCT visitor_hash ) AS unique_downloaders FROM {$table_name} WHERE kit_id IN ( {$id_placeholders} ) AND day BETWEEN %s AND %s GROUP BY kit_id",
			array_merge( $kit_ids, array( $first_day, $last_day ) )
		)
	);

	$map = array();
	foreach ( $rows as $row ) {
		$map[ (int) $row->kit_id ] = (int) $row->unique_downloaders;
	}

	return $map;
}
