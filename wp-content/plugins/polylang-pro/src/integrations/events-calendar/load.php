<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( ! defined( 'TRIBE_EVENTS_FILE' ) ) {
			// Run only if The Event Calendar is enabled.
			return;
		}

		PLL_Integrations::instance()->tec = new PLL_TEC();
		PLL_Integrations::instance()->tec->init( $polylang );
	}
);
