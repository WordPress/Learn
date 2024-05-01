<?php
/**
 * Loads the Student group module.
 *
 * @package sensei-student-groups
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-student-groups.php';

use Sensei_Pro_Student_Groups\Student_Groups;

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Student_Groups::class, 'init' ], 5 );
