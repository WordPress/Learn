<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

use WP_Syntex\Polylang_Pro\Modules\Machine_Translation;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Action;

if ( $polylang->model->has_languages() ) {
	// Ensure dependencies are loaded.
	require_once POLYLANG_PRO_DIR . '/modules/sync/load.php';
	require_once POLYLANG_PRO_DIR . '/modules/sync-post/load.php';
	require_once POLYLANG_DIR . '/modules/sync/load.php';

	$machine_translation_factory = new Machine_Translation\Factory( $polylang->model );

	if ( $machine_translation_factory->is_enabled() ) {
		$active_service = $machine_translation_factory->get_active_service();

		if ( $active_service && $polylang instanceof PLL_Admin ) {
			new PLL_Admin_Loader( $polylang, 'machine_translation', array( $active_service ) );
			$polylang->machine_translation_action = new Action( $polylang, $active_service );
		} elseif ( $active_service && $polylang instanceof PLL_REST_Request ) {
			$polylang->machine_translation = new Machine_Translation\Button_REST( $polylang, $active_service );
		}
	}

	if ( $polylang instanceof PLL_Settings ) {
		add_filter(
			'pll_settings_modules',
			function ( $modules ) {
				$k = array_search( PLL_Settings_Preview_Machine_Translation::class, $modules );
				if ( $k ) {
					unset( $modules[ $k ] );
					$modules['machine_translation'] = Machine_Translation\Module_Settings::class;
				}
				return $modules;
			},
			100
		);
	}
}
