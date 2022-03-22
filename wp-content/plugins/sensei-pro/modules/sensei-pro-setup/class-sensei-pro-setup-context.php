<?php
/**
 * \Sensei_WC_Paid_Courses\Sensei_Pro_Setup_Context;
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro_Setup;

/**
 * Provides setup context for Sensei Pro.
 */
class Sensei_Pro_Setup_Context extends \Sensei_Pro_Setup\Setup_Context {

	/**
	 * Returns a map of locales that is used for the setup wizard.
	 */
	public function get_setup_locales(): array {
		return [
			'page_title' => __( 'Sensei Pro - Setup Wizard', 'sensei-pro' ),
			'menu_title' => __( 'Sensei Pro', 'sensei-pro' ),
		];
	}

	/**
	 * Returns the plugin slug.
	 */
	public function get_plugin_slug(): string {
		return 'sensei-pro';
	}

	/**
	 * Returns the plugin url.
	 */
	public function get_plugin_url(): string {
		return untrailingslashit( plugins_url( '', SENSEI_PRO_PLUGIN_BASENAME ) );
	}

	/**
	 * Returns the plugin dir.
	 */
	public function get_plugin_dir(): string {
		return SENSEI_PRO_PLUGIN_DIR_PATH;
	}

	/**
	 * Returns the plugin's main filename.
	 */
	public function get_plugin_main_filename(): string {
		return $this->get_plugin_dir() . "$this->plugin_slug.php";
	}

	/**
	 * Returns the plugin version.
	 */
	public function get_plugin_version(): string {
		return SENSEI_PRO_VERSION;
	}
}
