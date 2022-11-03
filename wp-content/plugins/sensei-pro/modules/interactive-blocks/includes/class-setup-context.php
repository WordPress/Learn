<?php
/**
 * \Sensei_Pro_Interactive_Blocks\Setup_Context;
 *
 * @package sensei-pro
 * @since   1.2.0
 */

namespace Sensei_Pro_Interactive_Blocks;

require_once dirname( __DIR__ ) . '/sensei-pro-setup/class-setup-context.php';

/**
 * Provides setup context for Sensei Pro.
 */
class Setup_Context extends \Sensei_Pro_Setup\Setup_Context {

	/**
	 * Returns a map of locales that is used for the setup wizard.
	 */
	public function get_locales(): array {
		return [
			'page_title' => __( 'Sensei Blocks', 'sensei-pro' ),
			'menu_title' => __( 'Blocks', 'sensei-pro' ),
			'header'     => [
				'title' => __( 'Sensei Blocks', 'sensei-pro' ),
			],
		];
	}

	/**
	 * Returns the plugin slug.
	 */
	public function get_plugin_slug(): string {
		return 'sensei-interactive-blocks';
	}

	/**
	 * Returns the production assets url for the sensei-pro-setup module.
	 */
	public function get_setup_assets_url(): string {
		return SENSEI_IB_PLUGIN_DIR_URL . '/assets/dist/sensei-pro-setup';
	}

	/**
	 * Returns the plugin dir.
	 */
	public function get_plugin_dir(): string {
		return SENSEI_IB_PLUGIN_DIR_PATH;
	}

	/**
	 * Returns the plugin's main filename.
	 */
	public function get_plugin_main_filename(): string {
		return SENSEI_IB_PLUGIN_FILE;
	}

	/**
	 * Returns the plugin version.
	 */
	public function get_plugin_version(): string {
		return SENSEI_IB_VERSION;
	}

	/**
	 * Tells if the Sensei LMS should be installed.
	 */
	public function get_requires_sensei(): bool {
		return false;
	}
}
