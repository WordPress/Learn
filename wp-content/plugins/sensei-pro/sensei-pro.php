<?php
/**
 * Plugin Name: Sensei Pro
 * Plugin URI: https://senseilms.com/
 * Description: Whether you want to teach, tutor or train, we have you covered.
 * Version: 1.20.0
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 6.2
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 8.4
 * Sensei requires at least: 4.20.0
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Text Domain: sensei-pro
 * Domain Path: /lang
 *
 * @package sensei-pro
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do not check for conflicts if sensei-pro is part of woothemes-sensei.
// Because woothemes-sensei has own conflicts check.
if ( ! realpath( dirname( __FILE__ ) . '/../../woothemes-sensei.php' ) ) {
	require_once dirname( __FILE__ ) . '/includes/sensei-pro-conflicts-checker.php';
	if ( sensei_pro_has_conflicts() ) {
		return;
	}
}

define( 'SENSEI_PRO_VERSION', '1.20.0' );
define( 'SENSEI_PRO_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SENSEI_PRO_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SENSEI_PRO_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'SENSEI_PRO_HARD_MIN_SENSEI_VERSION', '4.6.0' );

// Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
// This is done before the dependency check to ensure the compatibility warning is not shown if the check do not pass.
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', SENSEI_PRO_PLUGIN_FILE, true );
		}
	}
);

require_once dirname( __FILE__ ) . '/includes/class-sensei-pro-dependency-checker.php';
if ( ! Sensei_Pro_Dependency_Checker::are_system_dependencies_met() ) {
	return;
}

// Sensei Pro Installer.
require_once dirname( __FILE__ ) . '/modules/installer/installer.php';
Sensei_Pro_Installer\Installer::instance()->init();

// Sensei Pro Setup.
require_once dirname( __FILE__ ) . '/includes/class-setup-context.php';
require_once dirname( __FILE__ ) . '/modules/sensei-pro-setup/sensei-pro-setup.php';
sensei_pro_setup_init( new Sensei_Pro\Setup_Context() );

// Load and init Sensei Pro main class.
require_once dirname( __FILE__ ) . '/includes/class-sensei-pro.php';

add_action( 'plugins_loaded', array( 'Sensei_Pro\Sensei_Pro', 'init' ), 4 );
