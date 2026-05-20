<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { // If uninstall not called from WordPress exit
	exit();
}

delete_option( '_transient_pll_translated_slugs' ); // Force deletion of the transient from the options table even in case external object cache is used.
