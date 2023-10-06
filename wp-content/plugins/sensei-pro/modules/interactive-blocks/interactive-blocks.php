<?php
/**
 * Loads the Interactive Blocks module.
 *
 * @package interactive-blocks
 */

use Sensei_Pro_Interactive_Blocks\Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
define( 'SENSEI_IB_VERSION', SENSEI_PRO_VERSION );
define( 'SENSEI_IB_PLUGIN_FILE', SENSEI_PRO_PLUGIN_FILE );
define( 'SENSEI_IB_PLUGIN_BASENAME', SENSEI_PRO_PLUGIN_BASENAME );
define( 'SENSEI_IB_PLUGIN_DIR_URL', SENSEI_PRO_PLUGIN_DIR_URL );
define( 'SENSEI_IB_PLUGIN_DIR_PATH', SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/interactive-blocks/' );
// phpcs:enable: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

/**
 * Sets up interactive blocks module.
 */
function sensei_pro_setup_interactive_blocks() {
	require_once __DIR__ . '/includes/tutor-chat/class-tutor-chat-service.php';
	require_once __DIR__ . '/includes/tutor-chat/class-tutor-chat-rest-api.php';
	require_once __DIR__ . '/includes/class-interactive-blocks.php';
	require_once __DIR__ . '/includes/class-assets-provider.php';

	$assets_provider = new Sensei_Pro_Interactive_Blocks\Assets_Provider(
		SENSEI_IB_PLUGIN_DIR_URL,
		SENSEI_PRO_PLUGIN_DIR_PATH,
		SENSEI_IB_VERSION,
		'sensei-pro',
		Interactive_Blocks::MODULE_NAME
	);

	Interactive_Blocks::init( $assets_provider );
}

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', 'sensei_pro_setup_interactive_blocks', 5 );
