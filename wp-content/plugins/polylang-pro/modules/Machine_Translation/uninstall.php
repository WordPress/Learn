<?php
/**
 * @package Polylang-Pro
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_metadata( 'user', 0, 'pll_machine_translation_deepl', '', true );
