<?php
/**
 * Loads the Sensei Pro AI module.
 *
 * @package sensei-pro-ai
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-sensei-pro-ai.php';
require_once __DIR__ . '/includes/services/class-question-generator-service.php';
require_once __DIR__ . '/includes/services/class-course-outline-service.php';
require_once __DIR__ . '/includes/rest-api/controllers/class-chat-gpt-controller.php';
require_once __DIR__ . '/includes/rest-api/controllers/class-course-outline-controller.php';

use Sensei_Pro_AI\Sensei_Pro_AI;

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Sensei_Pro_AI::instance(), 'init' ], 5 );
