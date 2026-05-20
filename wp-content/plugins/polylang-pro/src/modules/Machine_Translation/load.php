<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

use WP_Syntex\Polylang_Pro\Modules\Machine_Translation;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Posts\Action as Post_Action;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Posts\Button_REST;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Strings\Metabox as Strings_Metabox;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper\Machine_Translation as Capability;

if ( $polylang->model->has_languages() ) {
	// Ensure dependencies are loaded.
	require_once POLYLANG_PRO_DIR . '/src/modules/Capabilities/load.php';
	require_once POLYLANG_PRO_DIR . '/src/modules/sync/load.php';
	require_once POLYLANG_PRO_DIR . '/src/modules/sync-post/load.php';
	require_once POLYLANG_DIR . '/src/modules/sync/load.php';

	if ( current_user_can( Capability::CAPABILITY ) ) {
		$machine_translation_factory = new Machine_Translation\Factory( $polylang->model );

		if ( $machine_translation_factory->is_enabled() ) {
			$active_service = $machine_translation_factory->get_active_service();

			if ( $active_service && $polylang instanceof PLL_Admin ) {
				new PLL_Admin_Loader( $polylang, 'machine_translation', array( $active_service ) );
				$polylang->machine_translation_action = new Post_Action( $polylang, $active_service );
			} elseif ( $active_service && $polylang instanceof PLL_REST_Request ) {
				$polylang->machine_translation = new Button_REST( $polylang, $active_service );
			} elseif ( $active_service && $polylang instanceof PLL_Settings ) {
				$polylang->machine_translation = ( new Strings_Metabox( $polylang, $active_service ) )->init();
			}
		}
	}

	if ( $polylang instanceof PLL_Settings ) {
		add_filter(
			'pll_settings_modules',
			function ( $modules ) {
				$k = array_search( PLL_Settings_Preview_Machine_Translation::class, $modules, true );
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
