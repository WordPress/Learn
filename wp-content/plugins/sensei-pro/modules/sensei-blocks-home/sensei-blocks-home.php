<?php
/**
 * Loads the Sensei Home module for Sensei Blocks.
 *
 * @package sensei-blocks-home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/includes/class-sensei-lms-home.php';
require_once __DIR__ . '/includes/class-sensei-home.php';
require_once __DIR__ . '/includes/providers/class-help.php';
require_once __DIR__ . '/includes/class-rest-api.php';
