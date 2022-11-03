<?php
/**
 * Loads the Shared module.
 *
 * This module contains shared libraries/classes that can be used across all other modules.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.0.1
 */

use Sensei_Pro\Shared_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-shared-module.php';

/**
 * Initialises the shared module.
 */
function sensei_pro_shared_module_init() {
	Shared_Module::init( \Sensei_Pro\Modules\assets_loader( Shared_Module::MODULE_NAME ), SENSEI_PRO_PLUGIN_DIR_PATH . 'vendor/' );
}

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', 'sensei_pro_shared_module_init', 5 );
