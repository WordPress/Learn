<?php
/**
 * Sensei Pro Uninstall
 *
 * Uninstalls the plugin and associated modules and data.
 *
 * @package sensei-pro
 * @since 1.0.0
 *
 * @var string $plugin Plugin name being passed to `uninstall_plugin()`.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( class_exists( 'Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses' ) ) {
	// Another instance of WCPC is installed and activated on the current site or network.
	return;
}

define( 'SENSEI_PRO_PLUGIN_FILE', dirname( __DIR__ ) . '/' . $plugin );

require dirname( __FILE__ ) . '/modules/wc-paid-courses/wc-paid-courses.php';

if ( ! class_exists( 'Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses' ) ) {
	// We still want people to be able to delete WCPC if they don't meet dependencies.
	return;
}

// Fetch all sub-directories in the 'modules' folder.
$sensei_pro_modules_dir = dirname( __FILE__ ) . '/modules';
$sensei_pro_all_sub_dir = glob( $sensei_pro_modules_dir . '/*', GLOB_ONLYDIR );

foreach ( $sensei_pro_all_sub_dir as $sensei_pro_dir ) {
	$sensei_pro_uninstall_file = $sensei_pro_dir . '/uninstall-hooks.php';
	if ( file_exists( $sensei_pro_uninstall_file ) ) {
		require $sensei_pro_uninstall_file;
	}
}

require dirname( __FILE__ ) . '/includes/class-data-cleaner.php';

( new Sensei_Pro\Data_Cleaner() )->uninstall( $plugin );
