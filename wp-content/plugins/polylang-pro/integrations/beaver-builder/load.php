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
		if ( class_exists( 'FLBuilderLoader' ) ) {
			PLL_Integrations::instance()->flbuilder = new PLL_FLBuilder();
		}
	}
);
