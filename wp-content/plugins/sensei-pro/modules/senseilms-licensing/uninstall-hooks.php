<?php
/**
 * SenseiLMS Licensing Uninstall Hooks
 *
 * Adds the associated data to be deleted when uninstalled.
 * This includes options, post_meta, user_meta, transients and custom database tables.
 *
 * @package senseilms-licensing
 * @since   1.0.0
 */

if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
	return;
}

add_filter(
	'sensei_pro_data_cleaner_options',
	function( $options ) {
		require_once dirname( __FILE__ ) . '/includes/class-license-manager.php';
		$plugin_slug = basename( SENSEI_PRO_PLUGIN_FILE, '.php' );
		return array_merge(
			$options,
			[
				\SenseiLMS_Licensing\License_Manager::LICENSE_KEY_OPTION_PREFIX . $plugin_slug,
			]
		);
	}
);

add_filter(
	'sensei_pro_data_cleaner_transients',
	function( $transients ) {
		require_once dirname( __FILE__ ) . '/includes/class-senseilms-plugin-updater.php';
		require_once dirname( __FILE__ ) . '/includes/class-license-manager.php';
		$plugin_slug = basename( SENSEI_PRO_PLUGIN_FILE, '.php' );
		return array_merge(
			$transients,
			[
				\SenseiLMS_Licensing\SenseiLMS_Plugin_Updater::CACHE_KEY_PREFIX . $plugin_slug,
				\SenseiLMS_Licensing\License_Manager::CACHE_KEY_PREFIX . $plugin_slug,
			]
		);
	}
);
