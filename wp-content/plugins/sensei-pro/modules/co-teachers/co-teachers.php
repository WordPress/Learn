<?php
/**
 * Loads the Co-Teachers module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.3.0
 */

use Sensei_Pro_Co_Teachers\Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-co-teachers.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Co_Teachers::class, 'init' ], 5 );

Co_Teachers::instance();
