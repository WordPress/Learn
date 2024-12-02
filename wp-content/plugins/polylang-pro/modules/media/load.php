<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( $polylang->model->has_languages() ) {
	add_filter(
		'pll_settings_modules',
		function ( $modules ) {
			$k = array_search( 'PLL_Settings_Media', $modules );
			if ( $k ) {
				$modules[ $k ] = 'PLL_Settings_Advanced_Media';
			}
			return $modules;
		},
		0
	);

	add_action(
		'pll_init',
		function ( $polylang ) {
			if ( $polylang->options['media_support'] ) {
				if ( $polylang instanceof PLL_Admin ) {
					require_once POLYLANG_PRO_DIR . '/modules/bulk-translate/load.php';
				}

				if ( $polylang instanceof PLL_Admin || $polylang instanceof PLL_REST_Request ) {
					$polylang->advanced_media = new PLL_Admin_Advanced_Media( $polylang );
				}
			}
		}
	);
}
