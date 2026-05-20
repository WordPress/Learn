<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( ! class_exists( 'FLBuilderLoader' ) ) {
			// Run only if Beaver Builder is enabled.
			return;
		}

		if ( ! $polylang->model->has_languages() ) {
			// Run only if there is at least one language.
			return;
		}

		PLL_Integrations::instance()->flbuilder = new PLL_FLBuilder();
	}
);
