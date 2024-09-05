<?php
/**
 * Plugin Name: Sensei Blocks
 * Plugin URI: https://senseilms.com/
 * Description: A collection of interactive blocks for making your content and lessons more engaging.
 * Version: 1.4.5
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 6.3
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Text Domain: sensei-pro
 * Domain Path: /lang
 *
 * @package sensei-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/interactive-blocks-conflicts-checker.php';
if ( sensei_interactive_blocks_has_conflicts() ) {
	return;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
define( 'SENSEI_PRO_VERSION', '1.24.0' ); // Note: this is the current sensei-pro version this plugin was built from, needed so the correct language packs are loaded (in shared module).
define( 'SENSEI_IB_VERSION', '1.4.5' );
define( 'SENSEI_IB_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_IB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SENSEI_IB_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SENSEI_IB_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

require_once __DIR__ . '/includes/class-setup-context.php';
require_once __DIR__ . '/sensei-pro-setup/sensei-pro-setup.php';

sensei_pro_setup_init( new Sensei_Pro_Interactive_Blocks\Setup_Context() );

/**
 * Sets up interactive blocks.
 */
function sensei_interactive_blocks_setup_plugin() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	require_once __DIR__ . '/includes/tutor-chat/class-tutor-chat-service.php';
	require_once __DIR__ . '/includes/tutor-chat/class-tutor-chat-rest-api.php';
	require_once __DIR__ . '/includes/class-interactive-blocks.php';
	require_once __DIR__ . '/includes/class-assets-provider.php';
	require_once __DIR__ . '/shared-module/includes/class-shared-module.php';
	require_once __DIR__ . '/sensei-blocks-home/sensei-blocks-home.php';

	$shared_module_assets_provider = new Sensei_Pro_Interactive_Blocks\Assets_Provider(
		SENSEI_IB_PLUGIN_DIR_URL,
		SENSEI_IB_PLUGIN_DIR_PATH,
		SENSEI_IB_VERSION,
		'sensei-pro',
		\Sensei_Pro\Shared_Module::MODULE_NAME
	);

	\Sensei_Pro\Shared_Module::init( $shared_module_assets_provider, __DIR__ . '/vendor/' );

	$assets_provider = new Sensei_Pro_Interactive_Blocks\Assets_Provider(
		SENSEI_IB_PLUGIN_DIR_URL,
		SENSEI_IB_PLUGIN_DIR_PATH,
		SENSEI_IB_VERSION,
		'sensei-pro',
		Sensei_Pro_Interactive_Blocks\Interactive_Blocks::MODULE_NAME
	);

	Sensei_Pro_Interactive_Blocks\Interactive_Blocks::init( $assets_provider );
}

add_action( 'plugins_loaded', 'sensei_interactive_blocks_setup_plugin' );

/**
 * Loads the plugin textdomain.
 */
function sensei_interactive_blocks_load_textdomain() {
	load_plugin_textdomain( 'sensei-pro', false, dirname( SENSEI_IB_PLUGIN_BASENAME ) . '/lang' );
}

add_action( 'init', 'sensei_interactive_blocks_load_textdomain', 0 );
