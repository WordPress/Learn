<?php
/**
 * Plugin Name: GlotPress/WordPress Locales
 * Description: Defines <code>GP_Locale</code> and <code>GP_Locales</code> and extends them with custom locales used throughout wordpress.org.
 * License: GPLv2 or later
 */

namespace {
	require_once __DIR__ . '/locales/locales.php';
	require_once __DIR__ . '/locale-switcher/locale-switcher.php';
}

namespace WordPressdotorg\Locales {

	use GP_Locales, GP_Locale;

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

	/**
	 * Retrieves all available locales.
	 *
	 * @return GP_Locale[] Array of locale objects.
	 */
	function get_locales() {
		wp_cache_add_global_groups( array( 'locale-associations' ) );

		$wp_locales = wp_cache_get( 'locale-list', 'locale-associations' );
		if ( false === $wp_locales ) {
			$wp_locales = (array) $GLOBALS['wpdb']->get_col( 'SELECT locale FROM wporg_locales' );
			wp_cache_set( 'locale-list', $wp_locales, 'locale-associations' );
		}

		$wp_locales[] = 'en_US';
		$locales = array();

		foreach ( $wp_locales as $locale ) {
			$gp_locale = GP_Locales::by_field( 'wp_locale', $locale );
			if ( ! $gp_locale ) {
				continue;
			}

			$locales[ $locale ] = $gp_locale;
		}

		natsort( $locales );

		return $locales;
	}

	/**
	 * Get an array of locales with the locale code as key and the native name as value.
	 *
	 * @return array
	 */
	function get_locales_with_native_names() {
		$locales = get_locales();

		return wp_list_pluck( $locales, 'native_name', 'wp_locale' );
	}

	/**
	 * Get an array of locales with the locale code as key and the English name as value.
	 *
	 * @return array
	 */
	function get_locales_with_english_names() {
		$locales = get_locales();

		return wp_list_pluck( $locales, 'english_name', 'wp_locale' );
	}

	/**
	 * Get the name of a locale from the code.
	 *
	 * @param string $code      The locale code to look up. E.g. en_US.
	 * @param string $name_type Optional. 'native' or 'english'. Default 'native'.
	 *
	 * @return mixed|string
	 */
	function get_locale_name_from_code( $code, $name_type = 'native' ) {
		$function = __NAMESPACE__ . "\get_locales_with_{$name_type}_names";
		$locales  = $function();

		return $locales[ $code ] ?? '';
	}
}
