<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( $polylang->model->has_languages() ) {
	$polylang->sync_content = new PLL_Sync_Content( $polylang );
	$polylang->navigation = new PLL_Sync_Navigation( $polylang );
}
