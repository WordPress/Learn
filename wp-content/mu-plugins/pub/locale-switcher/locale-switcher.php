<?php

namespace WordPressdotorg\LocaleSwitcher;

use function WordPressdotorg\Locales\{ get_locales_with_native_names };

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\maybe_load', 99 );

/**
 * Hook up the functionality only if the Locale Detection plugin is activated.
 *
 * @return void
 */
function maybe_load() {
	if ( class_exists( '\WordPressdotorg\LocaleDetection\Detector' ) ) {
		add_action( 'admin_bar_menu', __NAMESPACE__ . '\admin_bar_node' );
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
		add_action( 'wp_print_footer_scripts', __NAMESPACE__ . '\locale_switcher_container' );
	}
}

/**
 * Add a Locale node to the admin bar on the front end.
 *
 * @param \WP_Admin_Bar $wp_admin_bar
 *
 * @return void
 */
function admin_bar_node( $wp_admin_bar ) {
	// This only needs to be shown on the front end.
	if ( is_admin() ) {
		return;
	}

	$all_locales    = get_locales_with_native_names();
	$current_locale = get_locale();

	$node = array(
		'id'     => 'locale-switcher',
		'parent' => 'top-secondary',
		'title'  => sprintf(
			__( '<span class="screen-reader-text">Current language:</span> %s', 'wporg' ),
			$all_locales[ $current_locale ]
		),
		'href'   => '#',
	);

	$wp_admin_bar->add_node( $node );
}

/**
 * Enqueue script and style assets.
 *
 * @return void
 */
function enqueue_assets() {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	$script_data = require __DIR__ . '/build/index.asset.php';

	wp_enqueue_style(
		'wporg-locale-switcher',
		plugins_url( 'build/style-index.css', __FILE__ ),
		array( 'wp-components' ),
		$script_data['version'],
		'screen'
	);

	wp_enqueue_script(
		'wporg-locale-switcher',
		plugins_url( 'build/index.js', __FILE__ ),
		$script_data['dependencies'],
		$script_data['version'],
		true
	);

	$locales = get_locales_with_native_names();
	ksort( $locales );
	$locale_options = array_reduce(
		array_keys( $locales ),
		function( $accumulator, $key ) use ( $locales ) {
			$accumulator[] = array(
				'label' => sprintf(
					// translators: 1: Native name for locale. 2: WP code for locale, e.g. en_US.
					__( '%1$s [%2$s]', 'wporg' ),
					$locales[ $key ],
					$key
				),
				'value' => $key,
			);

			return $accumulator;
		},
		array()
	);

	$locale_config = array(
		'initialValue' => get_locale(),
		'options'      => $locale_options,
	);

	wp_add_inline_script(
		'wporg-locale-switcher',
		'var wporgLocaleSwitcherConfig = ' . wp_json_encode( $locale_config ) . ';',
		'before'
	);
}

/**
 * Render a container for the locale switcher.
 *
 * @return void
 */
function locale_switcher_container() {
	echo '<div id="wporg-locale-switcher-container"></div>';
}
