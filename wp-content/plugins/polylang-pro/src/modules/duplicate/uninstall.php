<?php
/**
 * @package Polylang-Pro
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_metadata( 'user', 0, 'pll_duplicate_content', '', true );
