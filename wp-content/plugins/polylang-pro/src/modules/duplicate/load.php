<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( $polylang->model->has_languages() ) {
	if ( $polylang instanceof PLL_Admin ) {
		new PLL_Admin_Loader( $polylang, 'duplicate' );
		$polylang->duplicate_action = new PLL_Duplicate_Action( $polylang );
	} elseif ( $polylang instanceof PLL_REST_Request ) {
		$polylang->duplicate = new PLL_Duplicate_REST( $polylang );
	}
}
