<?php
/**
 * Loads the Advanced Quiz module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.0.0
 */

use Sensei_Pro_Advanced_Quiz\Advanced_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/includes/class-advanced-quiz.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Advanced_Quiz::class, 'init' ], 5 );

Advanced_Quiz::instance();
