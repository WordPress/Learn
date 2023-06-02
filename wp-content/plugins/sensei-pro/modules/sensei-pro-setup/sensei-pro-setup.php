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
 * Returns the Sensei Home page, for when the user installs Sensei Pro and already has Sensei LMS activated.
 *
 * @internal
 * @return string The URL to Sensei Home.
 */
function sensei_pro_get_home_url() {
	return admin_url( 'admin.php?page=sensei' );
}

/**
 * Verify if Sensei is already activated and add filter to change the setup URL
 * to Sensei Home.
 *
 * @internal
 */
function sensei_pro_detect_sensei_activated() {
	if ( \Sensei_Pro_Setup\Wizard::is_sensei_home_available() ) {
		add_filter( 'sensei_pro_wizard_setup_url', 'sensei_pro_get_home_url' );
	}
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

	add_action( 'init', 'sensei_pro_detect_sensei_activated' );
	\Sensei_Pro_Setup\Wizard::instance( $context )->init();

	// Sensei Home activation form.
	require_once dirname( __FILE__ ) . '/class-sensei-home-license-activation.php';
	\Sensei_Pro_Setup\Sensei_Home_License_Activation::instance( $context )->init();

	// Sensei WPCOM Marketplace License Manager.
	require_once dirname( __FILE__ ) . '/class-wpcom-marketplace-license-manager.php';
	\Sensei_Pro_Setup\WPCOM_Marketplace_License_Manager::instance()->init();
}

/**
 * Adds script to hook on Sensei's Setup Wizard.
 *
 * @param {string} $hook Name of the page that is being accessed. The function only will run when this is
 *                       'admin_page_sensei_setup_wizard'.
 */
function sensei_pro_setup_wizard_add_script( $hook ) {
	if ( ! defined( 'SENSEI_PRO_PLUGIN_DIR_URL' ) || 'admin_page_sensei_setup_wizard' !== $hook ) {
		// We do not want to enqueue this script on Sensei Interactive Blocks.
		return;
	}
	wp_enqueue_script(
		'sensei-pro-setup-wizard',
		SENSEI_PRO_PLUGIN_DIR_URL . '/assets/dist/sensei-pro-setup/setup-wizard-welcome-step.js',
		[ 'wp-hooks', 'wp-i18n' ],
		SENSEI_PRO_VERSION,
		true
	);
}

add_action( 'admin_enqueue_scripts', 'sensei_pro_setup_wizard_add_script', 1 );
