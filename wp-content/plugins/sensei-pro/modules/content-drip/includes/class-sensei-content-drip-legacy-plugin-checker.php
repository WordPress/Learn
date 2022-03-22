<?php
/**
 * File containing the class Sensei_Content_Drip_Legacy_Plugin_Checker.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sensei_Content_Drip_Legacy_Plugin_Checker
 */
class Sensei_Content_Drip_Legacy_Plugin_Checker {
	const LEGACY_PLUGIN_SLUG = 'sensei-content-drip/sensei-content-drip.php';

	/**
	 * Check if the legacy Content Drip plugin is active.
	 *
	 * @return bool
	 */
	public static function legacy_plugin_is_active() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return \is_plugin_active( self::LEGACY_PLUGIN_SLUG )
			|| \is_plugin_active_for_network( self::LEGACY_PLUGIN_SLUG );
	}

	/**
	 * Show an admin notice encouraging users to remove the legacy plugin.
	 */
	public static function show_notice() {
		add_filter( 'sensei_admin_notices', [ self::class, 'add_notice' ] );
	}

	/**
	 * Hook into `sensei_admin_notices` to add notice.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @param array $notices The Sensei notices.

	 * @return array The new array of notices.
	 */
	public static function add_notice( $notices ) {
		$notices['legacy-content-drip-is-active'] = [
			'type'       => 'site-wide',
			'icon'       => 'sensei',
			'style'      => 'error',
			'heading'    => __( 'Found legacy Content Drip plugin', 'sensei-pro' ),
			'message'    => __( 'Content Drip is now part of the Sensei Pro plugin. Please deactivate the Content Drip plugin in order to use the latest features.', 'sensei-pro' ),
			'conditions' => [
				[
					'type'         => 'user_cap',
					'capabilities' => [ 'install_plugins' ],
				],
				[
					'type'    => 'screens',
					'screens' => [ 'sensei*', 'plugins', 'plugins-network' ],
				],
			],
		];

		return $notices;
	}
}
