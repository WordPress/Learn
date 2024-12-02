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
		foreach ( glob( __DIR__ . '/modules/*/uninstall.php', GLOB_NOSORT ) as $uninstall_script ) {
			require $uninstall_script; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
	}
);

require __DIR__ . '/vendor/wpsyntex/polylang/uninstall.php';
