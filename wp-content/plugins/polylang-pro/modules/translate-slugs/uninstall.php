<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { // If uninstall not called from WordPress exit
	exit();
}

delete_transient( 'pll_translated_slugs' );
