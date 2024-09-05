<?php
/**
 * Loads the Premium Patterns module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.3.0
 */

use Sensei_Pro_Premium_Patterns\Premium_Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-premium-patterns.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Premium_Patterns::class, 'init' ], 5 );

Premium_Patterns::instance();
