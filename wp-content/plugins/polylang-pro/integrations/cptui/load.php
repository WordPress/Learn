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
		if ( defined( 'CPTUI_VERSION' ) ) {
			add_action( 'pll_init', array( PLL_Integrations::instance()->cptui = new PLL_CPTUI(), 'init' ) );
		}
	},
	0
);
