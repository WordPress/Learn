<?php
/**
 * Kickstarts the sensei-pro-setup module.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
	return;
}

// License Manager.
require_once dirname( __DIR__ ) . '/senseilms-licensing/senseilms-licensing.php';

// Plugin activation.
require_once dirname( __FILE__ ) . '/class-wizard.php';
require_once dirname( __FILE__ ) . '/class-setup-context.php';
require_once dirname( __FILE__ ) . '/class-sensei-pro-setup-context.php';
require_once dirname( __FILE__ ) . '/class-rest-api.php';

\Sensei_Pro_Setup\Wizard::instance( new \Sensei_Pro_Setup\Sensei_Pro_Setup_Context() )->init();
