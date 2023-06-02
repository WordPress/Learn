<?php
/**
 * Loads the tracking module.
 *
 * @package sensei-pro-tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Tracking\Tracking;

require_once __DIR__ . '/includes/class-tracking.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Tracking::instance(), 'init' ], 5 );
