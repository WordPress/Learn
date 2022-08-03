<?php
/**
 * Loader for Sensei Pro assets.
 *
 * @package sensei-pro
 * @since 1.0.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class for loading module assets. Extends the Sensei_Assets class for
 * registering and enqueuing assets, but takes a module name upon construction.
 * Assets are enqueued from the `/assets/dist/<module-name>/` directory.
 */
class Assets extends \Sensei_Assets {
	/**
	 * The module name.
	 *
	 * @var string
	 */
	protected $module_name;

	/**
	 * Constructor.
	 *
	 * @param string $module_name The module name.
	 */
	public function __construct( $module_name = '' ) {
		$plugin_url = SENSEI_PRO_PLUGIN_DIR_URL;
		$plugin_dir = dirname( __FILE__, 2 );
		$version    = SENSEI_PRO_VERSION;
		parent::__construct( $plugin_url, $plugin_dir, $version, 'sensei-pro' );

		$this->module_name = $module_name;
	}

	/**
	 * The path of the built asset file for this module.
	 *
	 * @param string $file The file. If none supplied, just get the directory.
	 *
	 * @return string The path.
	 */
	public function dist_path( $file = '' ) {
		if ( ! empty( $this->module_name ) ) {
			return path_join( $this->plugin_path, "assets/dist/$this->module_name/$file" );
		}

		return path_join( $this->plugin_path, "assets/dist/$file" );
	}

	/**
	 * The path of the source asset file for this module.
	 *
	 * @param string $file The file. If none supplied, just get the directory.
	 *
	 * @return string The path.
	 */
	public function src_path( $file = '' ) {
		if ( ! empty( $this->module_name ) ) {
			return path_join( $this->plugin_path, "modules/$this->module_name/assets/$file" );
		}

		return path_join( $this->plugin_path, "assets/$file" );
	}

	/**
	 * The URL of the built asset file for this module.
	 *
	 * @param string $filename The file. If none supplied, just get the directory.
	 *
	 * @return string The URL.
	 */
	public function asset_url( $filename = '' ) {
		if ( ! empty( $this->module_name ) ) {
			return rtrim( $this->plugin_url, '/' ) . "/assets/dist/$this->module_name/$filename";
		}

		return rtrim( $this->plugin_url, '/' ) . "/assets/dist/$filename";
	}
}
