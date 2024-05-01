<?php
/**
 * Loads the WC Paid Courses module.
 *
 * @package sensei-wc-paid-courses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include deprecated functions.
require_once __DIR__ . '/includes/deprecated-functions.php';

// Load WCPC.
require_once __DIR__ . '/includes/class-sensei-wc-paid-courses.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ 'Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses', 'init' ], 5 );

Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses::instance();
