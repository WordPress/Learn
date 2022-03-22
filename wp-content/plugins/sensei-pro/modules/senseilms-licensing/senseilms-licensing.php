<?php
/**
 * Loads the SenseiLMS Licensing module.
 *
 * @package senseilms-licensing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
	return;
}

// Initialises License Manager.
require_once dirname( __FILE__ ) . '/includes/class-license-manager.php';
SenseiLMS_Licensing\License_Manager::init( SENSEI_PRO_PLUGIN_FILE, SENSEI_PRO_VERSION );
