<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

$polylang->locale_fallback = new PLL_Locale_Fallback();
add_action( 'pll_init', array( $polylang->locale_fallback, 'init' ) );
