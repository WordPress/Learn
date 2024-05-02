<?php
/**
 * Loads the Block Visibility module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.5.0
 */

use Sensei_Pro_Block_Visibility\Block_Visibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-block-visibility.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Block_Visibility::class, 'init' ], 5 );
