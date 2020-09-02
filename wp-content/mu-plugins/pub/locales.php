<?php
/**
 * Plugin Name: GlotPress/WordPress Locales
 * Description: Defines <code>GP_Locale</code> and <code>GP_Locales</code> and extends them with custom locales used throughout wordpress.org.
 * License: GPLv2 or later
 */

namespace {
	require_once __DIR__ . '/locales/locales.php';
}

namespace WordPressdotorg\Locales {

	use GP_Locales;

	/**
	 * Sets available languages to all possible locales.
	 */
	function set_available_languages() {
		static $locales;

		if ( ! isset( $locales ) ) {
			$locales = GP_Locales::locales();
			$locales = array_column( $locales, 'wp_locale' );
			$locales = array_filter( $locales );
		}

		return $locales;
	}
	add_filter( 'get_available_languages', __NAMESPACE__ . '\set_available_languages', 10, 0 );
}
