<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( ! defined( 'CPTUI_VERSION' ) ) {
			// Run only if Custom Post Type UI is enabled.
			return;
		}

		PLL_Integrations::instance()->cptui = new PLL_CPTUI();
		PLL_Integrations::instance()->cptui->init( $polylang );
	}
);
