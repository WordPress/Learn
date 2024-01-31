<?php
/**
 * Loads the Tailored Course Outline module.
 *
 * @package sensei-pro-tailored-course-outline
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-tailored-course-outline.php';

use Sensei_Pro_Tailored_Course_Outline\Tailored_Course_Outline;

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Tailored_Course_Outline::class, 'init' ], 5 );
