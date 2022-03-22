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
	 * The setup wizard page slug.
	 *
	 * @var string
	 */
	public $plugin_slug = '';

	/**
	 * Setup locales.
	 *
	 * @var array
	 */
	public $locales = [];

	/**
	 * Plugin url
	 *
	 * @var string
	 */
	public $plugin_url = '';

	/**
	 * Plugin dir
	 *
	 * @var string
	 */
	public $plugin_dir = '';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $plugin_version = '';

	/**
	 * Constructor for Setup_Context
	 */
	public function __construct() {
		$this->locales        = $this->get_setup_locales();
		$this->plugin_slug    = $this->get_plugin_slug();
		$this->plugin_url     = $this->get_plugin_url();
		$this->plugin_dir     = $this->get_plugin_dir();
		$this->plugin_version = $this->get_plugin_version();
	}

	/**
	 * Returns a map of locales that is used for the setup wizard.
	 */
	abstract public function get_setup_locales(): array;

	/**
	 * Returns the plugin slug.
	 */
	abstract public function get_plugin_slug(): string;

	/**
	 * Returns the plugin url.
	 */
	abstract public function get_plugin_url(): string;

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
}
