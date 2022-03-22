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

require_once dirname( __FILE__ ) . '/includes/class-shared-module.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Shared_Module::class, 'init' ], 5 );

Shared_Module::instance();
