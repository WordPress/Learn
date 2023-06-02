<?php
/**
 * Loads the glossary module.
 *
 * @package sensei-pro-glossary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Glossary\Glossary;

require_once __DIR__ . '/includes/class-glossary.php';
require_once __DIR__ . '/includes/class-glossary-admin.php';
require_once __DIR__ . '/includes/class-glossary-entry.php';
require_once __DIR__ . '/includes/class-glossary-handler.php';
require_once __DIR__ . '/includes/class-glossary-markup-generator.php';
require_once __DIR__ . '/includes/class-glossary-repository.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Glossary::instance(), 'init' ], 5 );
