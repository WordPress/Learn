<?php
/**
 * File containing helper functions for Sensei Pro modules.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro\Modules;

/**
 * Get the Sensei_Assets loader for the given module.
 *
 * @param string $module_name The name of the module.
 *
 * @return string The URL.
 */
function assets_loader( $module_name ) {
	return new \Sensei_Pro\Assets( $module_name );
}
