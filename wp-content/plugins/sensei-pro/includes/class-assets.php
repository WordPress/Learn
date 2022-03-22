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
		$plugin_dir = dirname( dirname( __FILE__ ) );
		$version    = SENSEI_PRO_VERSION;
		parent::__construct( $plugin_url, $plugin_dir, $version );

		$this->module_name = $module_name;
	}

	/**
	 * Get the asset config for enqueuing.
	 *
	 * @param string $filename     The file name to enqueue.
	 * @param string $dependencies The asset dependencies.
	 * @param string $args         Other args for the WP enqueue function.
	 *
	 * @return array The asset config.
	 */
	public function asset_config( $filename, $dependencies = [], $args = null ) {
		$is_js             = preg_match( '/\.js$/', $filename );
		$basename          = preg_replace( '/\.\w+$/', '', $filename );
		$url               = $this->asset_url( $filename );
		$version           = $this->version;
		$asset_config_path = $this->dist_path( $basename . '.asset.php' );

		if ( file_exists( $asset_config_path ) ) {
			$asset_config = require $asset_config_path;

			// Only add generated dependencies for scripts.
			if ( $is_js ) {
				$dependencies = array_unique( array_merge( $dependencies, $asset_config['dependencies'] ) );
			}
			$version = $asset_config['version'];
		}

		return [
			'url'          => $url,
			'dependencies' => $dependencies,
			'version'      => $version,
			'type'         => $is_js ? 'script' : 'style',
			'args'         => null !== $args ? $args : ( $is_js ? false : 'all' ), // defaults for wp_enqueue_script or wp_enqueue_style.
		];
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
