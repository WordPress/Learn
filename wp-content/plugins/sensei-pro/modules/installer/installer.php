<?php
/**
 * Loads the Installer module.
 *
 * @package sensei-pro
 * @since   1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-schema.php';
require_once __DIR__ . '/includes/interface-migration.php';
require_once __DIR__ . '/includes/class-data-migrator.php';
require_once __DIR__ . '/includes/class-installer.php';
