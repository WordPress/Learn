<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

add_action(
	'pll_init',
	function ( $polylang ) {
		$polylang->rest_api = new PLL_REST_API( $polylang );
	}
);
