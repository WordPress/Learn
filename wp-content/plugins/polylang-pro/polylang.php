<?php
/**
 * Polylang Pro
 *
 * @package           Polylang-Pro
 * @author            WP SYNTEX
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Polylang Pro
 * Plugin URI:        https://polylang.pro
 * Description:       Adds multilingual capability to WordPress
 * Version:           3.6.5
 * Requires at least: 6.2
 * Requires PHP:      7.0
 * Author:            WP SYNTEX
 * Author URI:        https://polylang.pro
 * Text Domain:       polylang-pro
 * Domain Path:       /languages
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Copyright 2011-2019 Frédéric Demarle
 * Copyright 2019-2024 WP SYNTEX
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

define( 'POLYLANG_PRO', true );
define( 'POLYLANG_PRO_FILE', __FILE__ );
define( 'POLYLANG_PRO_DIR', __DIR__ );

if ( ! defined( 'POLYLANG_ROOT_FILE' ) ) {
	define( 'POLYLANG_ROOT_FILE', __FILE__ );
}

if ( defined( 'POLYLANG_BASENAME' ) ) {
	// The user is attempting to activate a second plugin instance, typically Polylang and Polylang Pro.
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	deactivate_plugins( POLYLANG_BASENAME, false, is_network_admin() ); // Deactivate the other plugin.

	// Add the deactivated plugin to the list of recent activated plugins.
	if ( ! is_network_admin() ) {
		update_option( 'recently_activated', array( POLYLANG_BASENAME => time() ) + (array) get_option( 'recently_activated' ) );
	} else {
		update_site_option( 'recently_activated', array( POLYLANG_BASENAME => time() ) + (array) get_site_option( 'recently_activated' ) );
	}
} else {
	define( 'POLYLANG_BASENAME', plugin_basename( __FILE__ ) ); // Plugin name as known by WP.
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/wpsyntex/polylang/polylang.php';

if ( empty( $_GET['deactivate-polylang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_action( 'pll_pre_init', array( new PLL_Pro(), 'init' ), 0 );
}
