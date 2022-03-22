<?php
/**
 * Loads the Course Expiration module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.0.1
 */

use Sensei_Pro_Course_Expiration\Course_Expiration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/includes/class-course-expiration.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Course_Expiration::class, 'init' ], 5 );

Course_Expiration::instance();
