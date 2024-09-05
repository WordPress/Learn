<?php

namespace WPOrg_Learn\Locale;

use function WPOrg_Learn\{ get_build_path, get_build_url, get_views_path };

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\textdomain' );
add_filter( 'wporg_learn_update_locale_data', __NAMESPACE__ . '\update_locale_data' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_assets' );
add_filter( 'wporg_locale_switcher_options', __NAMESPACE__ . '\locale_switcher_options' );
add_filter( 'posts_clauses', __NAMESPACE__ . '\wporg_archive_query_prioritize_locale', 10, 2 );

if ( ! wp_next_scheduled( 'wporg_learn_update_locale_data' ) ) {
	wp_schedule_event( time(), 'hourly', 'wporg_learn_update_locale_data' );
}

/**
 * Load the wporg-learn textdomain.
 *
 * The pomo files for wporg-learn are in languages/themes, even though the translation project includes strings
 * from both a theme and a plugin.
 *
 * @return void
 */
function textdomain() {
	load_theme_textdomain( 'wporg-learn' );
}

/**
 * Update the locale data for the wporg-learn text domain.
 *
 * @return void
 */
function update_locale_data() {
	$gp_api           = 'https://translate.wordpress.org';
	$gp_project       = 'meta/learn-wordpress';
	$set_response     = wp_remote_get(
		"$gp_api/api/projects/$gp_project",
		array(
			'timeout' => 90,
		)
	);
	$body             = json_decode( wp_remote_retrieve_body( $set_response ) );
	$translation_sets = isset( $body->translation_sets ) ? $body->translation_sets : false;

	if ( ! $translation_sets ) {
		trigger_error( 'Translation sets missing from response body.' );
		return;
	}

	update_option( 'wporg-learn_locale_data', $translation_sets );
}

/**
 * Register style and script assets for later enqueueing.
 */
function register_assets() {
	// Locale notice script.
	wp_enqueue_script(
		'locale-notice',
		get_build_url() . 'locale-notice.js',
		array( 'jquery', 'utils' ),
		filemtime( get_build_path() . '/locale-notice.js' ),
		array( 'strategy' => 'defer' )
	);

	wp_localize_script(
		'locale-notice',
		'WPOrgLearnLocaleNotice',
		array(
			'cookie' => array(
				'expires' => YEAR_IN_SECONDS,
				'cpath'   => SITECOOKIEPATH,
				'domain'  => '',
				'secure'  => true,
			),
		)
	);
}

/**
 * Renders a notice when a locale isn't fully translated.
 */
function locale_notice() {
	$locale_data = get_option( 'wporg-learn_locale_data', array() );

	if ( empty( $locale_data ) ) {
		return;
	}

	$current_locale = get_locale();
	$statuses       = wp_list_pluck( $locale_data, 'percent_translated', 'wp_locale' );
	$mapped_locales = wp_list_pluck( $locale_data, 'locale', 'wp_locale' );
	$threshold      = 90;
	$is_dismissed   = ! empty( $_COOKIE['wporg-learn-locale-notice-dismissed'] );

	if ( isset( $statuses[ $current_locale ] ) && absint( $statuses[ $current_locale ] ) <= $threshold && ! $is_dismissed ) {
		$contribute_url = 'https://translate.wordpress.org/projects/meta/learn-wordpress/';

		if ( isset( $mapped_locales[ $current_locale ] ) ) {
			$contribute_url .= $mapped_locales[ $current_locale ] . '/default';
		}

		require get_views_path() . 'locale-notice.php';
	}
}

/**
 * Modify the locale switcher options.
 *
 * @param array $options
 *
 * @return array
 */
function locale_switcher_options( $options ) {
	$options = array_map(
		function( $locale ) {
			$locale['label'] .= " [{$locale['value']}]";

			return $locale;
		},
		$options
	);

	return $options;
}

/**
 * Modify post type archive queries to prioritize content in the user's locale.
 *
 * @param array    $clauses
 * @param WP_Query $query
 *
 * @return array
 */
function wporg_archive_query_prioritize_locale( $clauses, $query ) {
	if ( is_admin() || is_feed() ) {
		return $clauses;
	}

	$locale = get_locale();

	if ( ! $locale ) {
		return $clauses;
	}

	if ( $query->is_post_type_archive( 'wporg_workshop' ) ) {
		return wporg_tutorials_query_prioritize_locale( $clauses, $locale );
	}

	$current_theme = wp_get_theme();
	$theme_slug = $current_theme->get_stylesheet();

	if ( $query->is_post_type_archive( 'course' ) || $query->is_post_type_archive( 'lesson' ) || $query->is_post_type_archive( 'lesson-plan' ) ) {
		return wporg_query_prioritize_locale( $clauses, $locale );
	}

	return $clauses;
}

/**
 * Modify the workshop post type archive query to prioritize workshops in the user's locale.
 * DEPRECATED: this function has been superseded by wporg_query_prioritize_locale, which handles language post meta being empty or null.
 *
 * In order to show all workshops, but with the ones that are presented/captioned in the user's locale shown first, we
 * need to modify the posts query in ways that can't be done through the WP_Query or WP_Meta_Query APIs. Instead, here,
 * we're filtering the individual clauses of the query to add the pieces we need.
 *
 * Examples, slightly truncated for simplicity:
 *
 * Before:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.ID
 * FROM wp_posts
 * WHERE 1=1
 * AND wp_posts.post_type = 'wporg_workshop'
 * ORDER BY wp_posts.post_date DESC
 *
 * After:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.*,
 *   MAX( IF( pmeta.meta_key = 'language' AND pmeta.meta_value LIKE 'art_%', 1, 0 ) ) AS has_language,
 *   MAX( IF( pmeta.meta_key = 'video_caption_language' AND pmeta.meta_value LIKE 'art_%', 1, 0 ) ) AS has_caption
 * FROM wp_posts
 * INNER JOIN wp_postmeta pmeta ON ( wp_posts.ID = pmeta.post_id )
 * WHERE 1=1
 * AND wp_posts.post_type = 'wporg_workshop'
 * GROUP BY wp_posts.ID
 * ORDER BY has_language DESC, has_caption DESC, wp_posts.post_date DESC
 *
 * @param array  $clauses
 * @param string $locale
 *
 * @return array
 */
function wporg_tutorials_query_prioritize_locale( $clauses, $locale ) {
	global $wpdb;

	$locale_root = preg_replace( '#^([a-z]{2,3}_?)[a-zA-Z_-]*#', '$1', $locale, -1, $count );

	if ( $count ) {
		/**
		 * $clauses['fields'] contains the SELECT part of the query.
		 *
		 * The extra fields clauses are calculated fields that will contain a `1` if the workshop post row has a postmeta
		 * value that matches the locale root. The MAX() and the groupby clause below ensure that all the rows for a
		 * given workshop are consolidated into one with the highest value in the calculated column. Without the
		 * grouping, there would be a separate row for each postmeta value for each workshop post.
		 */
		$clauses['fields'] .= ",
			MAX( IF( pmeta.meta_key = 'language' AND pmeta.meta_value LIKE '{$locale_root}%', 1, 0 ) ) AS has_language
		";
		$clauses['fields'] .= ",
			MAX( IF( pmeta.meta_key = 'video_caption_language' AND pmeta.meta_value LIKE '{$locale_root}%', 1, 0 ) ) AS has_caption
		";
		$clauses['join']   .= " INNER JOIN {$wpdb->postmeta} pmeta ON ( {$wpdb->posts}.ID = pmeta.post_id )";
		// This orderby clause ensures that the workshops are sorted by the values in the calculated columns first.
		$clauses['orderby'] = 'has_language DESC, has_caption DESC, ' . $clauses['orderby'];

		if ( false === strpos( $clauses['groupby'], "{$wpdb->posts}.ID" ) ) {
			$clauses['groupby'] = "{$wpdb->posts}.ID";
		}
	}

	return $clauses;
}

/**
 * Modify the post type archive query to prioritize posts in the user's locale.
 *
 * In order to show all posts, but with the ones that are presented in the user's locale shown first, we
 * need to modify the posts query in ways that can't be done through the WP_Query or WP_Meta_Query APIs. Instead, here,
 * we're filtering the individual clauses of the query to add the pieces we need.
 *
 * Handles cases where the language post meta is empty or null and treats them as being english, if the locale is english.
 * This matches the default value for the language post meta field.
 *
 * Examples, slightly truncated for simplicity:
 *
 * Before:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.ID
 * FROM wp_posts
 * WHERE 1=1
 * AND wp_posts.post_type = 'lesson'
 * ORDER BY wp_posts.post_date DESC
 *
 * After:
 * SELECT SQL_CALC_FOUND_ROWS wp_posts.*,
 *   MAX(
 *     CASE
 *       WHEN pmeta.meta_key = 'language' AND pmeta.meta_value LIKE 'en_%' THEN 1
 *       WHEN (pmeta.meta_key = 'language' AND pmeta.meta_value = '') OR pmeta.meta_key IS NULL THEN 1
 *       ELSE 0
 *     END
 *   ) AS has_language,
 * FROM wp_posts
 * LEFT JOIN wp_postmeta pmeta ON ( wp_posts.ID = pmeta.post_id AND pmeta.meta_key = 'language' )
 * WHERE 1=1
 * AND wp_posts.post_type = 'lesson'
 * ORDER BY has_language DESC, wp_posts.post_date DESC
 * GROUP BY wp_posts.ID
 *
 * @param array  $clauses
 * @param string $locale
 *
 * @return array
 */
function wporg_query_prioritize_locale( $clauses, $locale ) {
	global $wpdb;

	$locale_root = preg_replace( '#^([a-z]{2,3}_?)[a-zA-Z_-]*#', '$1', $locale, -1, $count );

	if ( $count ) {
		/**
		 * $clauses['fields'] contains the SELECT part of the query.
		 *
		 * The extra fields clause is calculated, and will contain a `1` if the post row has a postmeta
		 * value that matches the locale root, or if the locale is english and the postmeta value is empty or null.
		 * The MAX() and the groupby clause below ensure that all the rows for a given post are consolidated into
		 * one, with the highest value in the calculated column.
		 * Without the grouping, there would be a separate row for each postmeta value for each post.
		 */
		$is_english = strpos( $locale_root, 'en_' ) === 0;

		$clauses['fields'] .= ",
			MAX(
				CASE
					WHEN pmeta.meta_key = 'language' AND pmeta.meta_value LIKE '{$locale_root}%' THEN 1
					WHEN (pmeta.meta_key = 'language' AND pmeta.meta_value = '') OR pmeta.meta_key IS NULL THEN " . ( $is_english ? '1' : '0' ) . '
					ELSE 0
				END
			) AS has_language
		';
		$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} pmeta ON ( {$wpdb->posts}.ID = pmeta.post_id AND pmeta.meta_key = 'language' )";
		$clauses['orderby'] = 'has_language DESC, ' . $clauses['orderby'];
		$clauses['groupby'] = "{$wpdb->posts}.ID";
	}

	return $clauses;
}
