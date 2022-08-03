<?php
/**
 * Kickstarts the sensei-pro-setup module.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initializes plugin licensing.
 *
 * @param Sensei_Pro_Setup\Setup_Context $context The setup context class.
 */
function sensei_pro_setup_init( $context ) {
	if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
		return;
	}

	require_once dirname( __DIR__ ) . '/senseilms-licensing/senseilms-licensing.php';
	SenseiLMS_Licensing\License_Manager::init( $context->get_plugin_main_filename(), $context->get_plugin_version() );

	// Plugin activation.
	require_once dirname( __FILE__ ) . '/class-wizard.php';
	require_once dirname( __FILE__ ) . '/class-rest-api.php';
	\Sensei_Pro_Setup\Wizard::instance( $context )->init();
}
