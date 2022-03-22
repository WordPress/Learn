<?php
/**
 * File containing the class \Sensei_Pro\Sensei_Pro.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Sensei Pro class.
 *
 * @class Sensei_Pro
 */
final class Sensei_Pro {
	/**
	 * Instance of class.
	 *
	 * @var Sensei_Pro
	 */
	private static $instance;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin_dir = dirname( __DIR__ );
	}

	/**
	 * Initializes the class and plugin.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$instance = self::instance();

		$skip_plugin_deps_check = defined( 'SENSEI_PRO_SKIP_DEPS_CHECK' ) && SENSEI_PRO_SKIP_DEPS_CHECK;

		if ( ! $skip_plugin_deps_check && ! \Sensei_Pro_Dependency_Checker::are_plugin_dependencies_met() ) {
			return;
		}

		$instance->include_dependencies();

		// Initialize classes.
		Language_Packs::instance()->init();
		Upsells::instance()->init();

		$instance->load_modules();
	}

	/**
	 * Load all modules.
	 */
	public function load_modules() {
		// Load modules.
		foreach ( $this->modules() as $module ) {
			$this->load_module( $module );
		}
	}

	/**
	 * Get the list of modules.
	 *
	 * @return array
	 */
	private function modules() {
		return [
			'shared-module', // Shared module must be loaded first since other modules may depend on it.
			'senseilms-licensing',
			'wc-paid-courses',
			'content-drip',
			'advanced-quiz',
			'course-expiration',
		];
	}

	/**
	 * Load a module.
	 *
	 * @param string $module The module name.
	 */
	private function load_module( $module ) {
		require $this->plugin_dir . "/modules/$module/$module.php";
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once $this->plugin_dir . '/modules/module-functions.php';
		include_once $this->plugin_dir . '/includes/class-assets.php';
		include_once $this->plugin_dir . '/includes/class-language-packs.php';
		include_once $this->plugin_dir . '/includes/class-upsells.php';
	}
}
