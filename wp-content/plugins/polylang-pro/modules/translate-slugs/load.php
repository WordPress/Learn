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
			$k = array_search( 'PLL_Settings_Preview_Translate_Slugs', $modules );
			if ( $k ) {
				$modules[ $k ] = 'PLL_Settings_Translate_Slugs';
			}
			return $modules;
		},
		20 // After Polylang.
	);

	if ( get_option( 'permalink_structure' ) ) {
		$slugs_model = new PLL_Translate_Slugs_Model( $polylang );

		if ( $polylang instanceof PLL_Frontend ) {
			$polylang->translate_slugs = new PLL_Frontend_Translate_Slugs( $slugs_model, $polylang->curlang );
		} else {
			$curlang = isset( $polylang->curlang ) ? $polylang->curlang : null;
			$polylang->translate_slugs = new PLL_Translate_Slugs( $slugs_model, $curlang );
		}
	}
}
