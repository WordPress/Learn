<?php
/**
 * Loads the Content Drip module.
 *
 * @package sensei-pro
 * @author Automattic
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check for legacy Content Drip plugin.
require_once dirname( __FILE__ ) . '/includes/class-sensei-content-drip-legacy-plugin-checker.php';
if ( Sensei_Content_Drip_Legacy_Plugin_Checker::legacy_plugin_is_active() ) {
	Sensei_Content_Drip_Legacy_Plugin_Checker::show_notice();
	return;
}

// TODO: Remove need for these plugins.
define( 'SENSEI_CONTENT_DRIP_PLUGIN_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/includes/class-sensei-content-drip.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ 'Sensei_Content_Drip', 'init' ], 5 );

Sensei_Content_Drip::instance();
