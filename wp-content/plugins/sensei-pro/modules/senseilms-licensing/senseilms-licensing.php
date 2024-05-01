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
require_once __DIR__ . '/includes/class-license-manager.php';
