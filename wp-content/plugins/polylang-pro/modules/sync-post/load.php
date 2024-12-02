<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( $polylang->model->has_languages() ) {
	$polylang->sync_post_model = new PLL_Sync_Post_Model( $polylang );

	if ( $polylang instanceof PLL_Admin ) {
		require_once POLYLANG_PRO_DIR . '/modules/bulk-translate/load.php';
	}

	if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		$polylang->sync_post = new PLL_Sync_Post( $polylang );
	} elseif ( $polylang instanceof PLL_Admin ) {
		new PLL_Admin_Loader( $polylang, 'sync_post' );
	} elseif ( $polylang instanceof PLL_REST_Request ) {
		$polylang->sync_post = new PLL_Sync_Post_REST( $polylang );
	} else {
		$polylang->sync_post = new PLL_Sync_Post( $polylang );
	}
}
