<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( ( $polylang instanceof PLL_Settings || $polylang instanceof PLL_Admin ) && $polylang->model->has_languages() ) {
	if ( $polylang instanceof PLL_Admin ) {
		require_once POLYLANG_PRO_DIR . '/modules/bulk-translate/load.php';
	}
	require_once POLYLANG_PRO_DIR . '/modules/sync/load.php';
	require_once POLYLANG_DIR . '/modules/sync/load.php';

	$polylang->import_export = new PLL_Import_Export( $polylang );
}
