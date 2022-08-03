<?php
/**
 * \Sensei_WC_Paid_Courses\Sensei_Pro_Setup\Setup_Context_Provider;
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro_Setup;

/**
 * Provides plugin specific context.
 */
abstract class Setup_Context {
	/**
	 * Returns a map of locales that is used for the setup wizard.
	 */
	abstract public function get_locales(): array;

	/**
	 * Returns the plugin slug.
	 */
	abstract public function get_plugin_slug(): string;

	/**
	 * Returns the production assets url for the sensei-pro-setup module.
	 */
	abstract public function get_setup_assets_url(): string;

	/**
	 * Returns the plugin dir.
	 */
	abstract public function get_plugin_dir(): string;

	/**
	 * Returns the plugin version.
	 */
	abstract public function get_plugin_version(): string;

	/**
	 * Returns the main plugin filename.
	 */
	abstract public function get_plugin_main_filename(): string;

	/**
	 * Tells if Sensei LMS plugin needs to be installed.
	 */
	abstract public function get_requires_sensei(): bool;
}
