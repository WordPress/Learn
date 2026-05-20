<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( 'Divi' !== get_template() && ! defined( 'ET_BUILDER_PLUGIN_VERSION' ) ) {
			// Run only if Divi Builder is enabled.
			return;
		}

		if ( ! $polylang->model->has_languages() ) {
			// Run only if there is at least one language.
			return;
		}

		PLL_Integrations::instance()->divi_builder = new PLL_Divi_Builder();
	}
);
