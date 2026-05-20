<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( PLL_COOKIE && $polylang instanceof PLL_Frontend && $polylang->model->has_languages() ) {
	$class = array( 2 => 'PLL_Xdata_Subdomain', 3 => 'PLL_Xdata_Domain' );
	if ( isset( $class[ $polylang->options['force_lang'] ] ) ) {
		$polylang->xdata = new $class[ $polylang->options['force_lang'] ]( $polylang );
	}
}
