<?php
/**
 * Plugin Name: Sensei Pro
 * Plugin URI: https://senseilms.com/
 * Description: Whether you want to teach, tutor or train, we have you covered.
 * Version: 1.0.2
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 5.7
 * Tested up to: 5.9
 * Requires PHP: 7.0
 * WC requires at least: 4.0
 * WC tested up to: 6.1.1
 * Author: Automattic
 * Author URI: https://senseilms.com/
 * Text Domain: sensei-pro
 * Domain Path: /lang
 *
 * @package sensei-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do not check for conflicts if sensei-pro is part of woothemes-sensei.
// Because woothemes-sensei has own conflicts check.
if ( ! realpath( dirname( __FILE__ ) . '/../../woothemes-sensei.php' ) ) {
	require_once dirname( __FILE__ ) . '/includes/class-sensei-pro-conflicts-checker.php';
	if ( Sensei_Pro_Conflicts_Checker::conflicts_with_woothemes_sensei() ) {
		return;
	}
}

define( 'SENSEI_PRO_VERSION', '1.0.2' );
define( 'SENSEI_PRO_PLUGIN_FILE', __FILE__ );
define( 'SENSEI_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SENSEI_PRO_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SENSEI_PRO_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

require_once dirname( __FILE__ ) . '/includes/class-sensei-pro-dependency-checker.php';

if ( ! Sensei_Pro_Dependency_Checker::are_system_dependencies_met() ) {
	return;
}

// Sensei Pro Setup.
require_once dirname( __FILE__ ) . '/modules/sensei-pro-setup/sensei-pro-setup.php';

// Load and init Sensei Pro main class.
require_once dirname( __FILE__ ) . '/includes/class-sensei-pro.php';

add_action( 'plugins_loaded', array( 'Sensei_Pro\Sensei_Pro', 'init' ), 4 );
