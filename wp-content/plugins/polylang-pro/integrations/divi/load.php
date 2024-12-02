<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

add_action(
	'plugins_loaded',
	function () {
		if ( ( 'Divi' === get_template() || defined( 'ET_BUILDER_PLUGIN_VERSION' ) ) ) {
			PLL_Integrations::instance()->divi_builder = new PLL_Divi_Builder();
		}
	}
);
