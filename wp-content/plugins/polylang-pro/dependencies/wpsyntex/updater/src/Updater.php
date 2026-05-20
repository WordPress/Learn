<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

use PLL_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Updater's main object for the given project.
 *
 * @since 1.0
 */
class Updater {
	/**
	 * @var License
	 */
	public $license;

	/**
	 * @var Translations
	 */
	public $translations;

	/**
	 * @var Wizard_Licenses_Step|null
	 */
	public $wizard_licenses_step;

	/**
	 * Constructor.
	 *
	 * Must be instantiated before `pll_init`.
	 * Should be instantiated in admin context (`\PLL_Admin_Base`).
	 *
	 * @since 1.0
	 *
	 * @param string $file        The plugin file.
	 * @param string $name        The plugin name.
	 * @param string $version     The plugin version.
	 * @param string $text_domain Text domain for translations.
	 */
	public function __construct( string $file, string $name, string $version, string $text_domain ) {
		$this->license      = new License( $file, $name, $version );
		$this->translations = new Translations( $text_domain );
		add_action( 'pll_init', array( $this, 'load_wizard' ) );
	}

	/**
	 * Loads the wizard's step.
	 *
	 * @since 1.0
	 *
	 * @param PLL_Base $polylang Polylang object.
	 * @return void
	 */
	public function load_wizard( $polylang ): void {
		if ( ! empty( $polylang->wizard ) ) {
			$this->wizard_licenses_step = new Wizard_Licenses_Step( $polylang->wizard );
		}
	}

	/**
	 * Returns the updater version.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_version(): string {
		// Cache the version to avoid reading the file multiple times.
		static $version = null;

		if ( null === $version ) {
			$version = require __DIR__ . '/../version.php';
		}

		return $version;
	}
}
