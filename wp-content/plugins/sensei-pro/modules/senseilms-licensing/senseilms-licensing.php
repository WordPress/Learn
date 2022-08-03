<?php
/**
 * Loads the SenseiLMS Licensing module.
 *
 * @package senseilms-licensing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialises License Manager.
require_once dirname( __FILE__ ) . '/includes/class-license-manager.php';
