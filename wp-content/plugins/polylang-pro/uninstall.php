<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { // If uninstall is not called from WordPress exit.
	exit;
}

add_action(
	'pll_uninstall',
	function () {
		// Executes each module's uninstall script, if it exists.
		$modules = require __DIR__ . '/src/modules/module-build.php';
		foreach ( $modules as $module ) {
			if ( file_exists( __DIR__ . "/src/modules/{$module}/uninstall.php" ) ) {
				require __DIR__ . "/src/modules/{$module}/uninstall.php";
			}
		}

		// Deletes Updater's cache options on uninstallation.
		require __DIR__ . '/dependencies/wpsyntex/updater/uninstall.php';
	}
);

require __DIR__ . '/vendor/wpsyntex/polylang/uninstall.php';
