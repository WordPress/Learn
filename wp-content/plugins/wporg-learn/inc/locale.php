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
add_filter( 'wp_headers', __NAMESPACE__ . '\disable_caching' );

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
	// Locale switcher script.
	wp_register_script(
		'locale-notice',
		get_build_url() . '/locale-notice.js',
		array( 'jquery', 'utils' ),
		filemtime( get_build_path() . '/locale-notice.js' ),
		true
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
 * Disable nginx caching when locale switching is available.
 *
 * The nginx cache currently doesn't vary on the value of the cookie that gets set when a locale other than en_US is
 * chosen. This causes problems when, e.g. a user visits the page with their browser language set to de_DE, which gets
 * cached by nginx, and then another user visits with their browser set to en_US, and they are served the page in
 * German regardless of they choose something else in the locale switcher.
 *
 * nginx does respect the Cache-Control header, though, so this offers a quick, hacky fix to the problem by turning
 * off caching altogether.
 *
 * This should be removed if a way is found to vary the cache by the cookie value, e.g. to include the cookie value
 * in the cache key as suggested in the discussion of this systems request:
 * https://make.wordpress.org/systems/2021/03/26/vary-nginx-cache-by-wporg_locale-cookie-value/
 *
 * @param array $headers
 *
 * @return array
 */
function disable_caching( $headers ) {
	if ( class_exists( '\WordPressdotorg\LocaleDetection\Detector' ) ) {
		$headers['Cache-Control'] = 'no-cache';
	}

	return $headers;
}
