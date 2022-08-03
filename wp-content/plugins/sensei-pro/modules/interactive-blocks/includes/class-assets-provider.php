<?php
/**
 * Assets_Provider
 * Handles script and stylesheet loading.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets_Provider
 * Loading CSS and Javascript files, copied from Sensei_Assets
 *
 * @package Core
 * @author  Automattic
 * @since   1.0.0
 */
class Assets_Provider {

	/**
	 * The URL for the plugin.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Plugin location.
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin text domain.
	 *
	 * @var string
	 */
	private $text_domain;

	/**
	 * Module name.
	 *
	 * @var string
	 */
	private $module_name;

	/**
	 * Assets_Provider constructor.
	 *
	 * @param string $plugin_url  The URL for the plugin.
	 * @param string $plugin_path Plugin location.
	 * @param string $version     Plugin version.
	 * @param string $text_domain Plugin text domain.
	 * @param string $module_name The module name.
	 */
	public function __construct( string $plugin_url, string $plugin_path, string $version, string $text_domain, string $module_name = null ) {
		$this->plugin_url  = $plugin_url;
		$this->plugin_path = $plugin_path;
		$this->version     = $version;
		$this->text_domain = $text_domain;
		$this->module_name = $module_name;
	}

	/**
	 * Enqueue a script or stylesheet with wp_enqueue_script/wp_enqueue_style.
	 *
	 * @param string      $handle       Unique name of the asset.
	 * @param string      $filename     The filename.
	 * @param array       $dependencies Dependencies.
	 * @param bool|string $args         In footer flag (script) or media type (style).
	 */
	public function enqueue( string $handle, string $filename, array $dependencies = [], $args = null ) {

		$config = $this->asset_config( $filename, $dependencies, $args );
		$this->call_wp( 'wp_enqueue', $handle, $config );

	}

	/**
	 * Register a script or stylesheet with wp_register_script/wp_register_style.
	 *
	 * @param string|null $handle       Unique name of the asset.
	 * @param string      $filename     The filename.
	 * @param array       $dependencies Dependencies.
	 * @param null        $args         Asset arguments.
	 */
	public function register( $handle, string $filename, array $dependencies = [], $args = null ) {
		$config = $this->asset_config( $filename, $dependencies, $args );

		$this->call_wp( 'wp_register', $handle, $config );
	}

	/**
	 * Enqueue a registered script.
	 *
	 * @param string $handle Unique name of the script.
	 */
	public function enqueue_script( $handle ) {
		wp_enqueue_script( $handle );
	}

	/**
	 * Enqueue a registered stylesheet.
	 *
	 * @param string $handle Unique name of the stylesheet.
	 */
	public function enqueue_style( $handle ) {
		wp_enqueue_style( $handle );
	}

	/**
	 * Call the wrapped WordPress core function with _type postfix
	 *
	 * @param string $action wp_enqueue or wp_register.
	 * @param string $handle Unique handle for the asset.
	 * @param array  $config Asset information.
	 */
	private function call_wp( string $action, string $handle, array $config ) {
		call_user_func( $action . '_' . $config['type'], $handle, $config['url'], $config['dependencies'], $config['version'], $config['args'] );

		if ( 'script' === $config['type'] && in_array( 'wp-i18n', $config['dependencies'], true ) ) {
			wp_set_script_translations( $handle, $this->text_domain );
		}
	}

	/**
	 * Builds asset metadata for a given file.
	 * Loads dependencies and version hash tracked by the build process from [filename].asset.php
	 *
	 * @param string      $filename        The filename.
	 * @param array       $dependencies    Dependencies.
	 * @param bool|string $wp_enqueue_args Argument passed to wp_enqueue_script or wp_enqueue_style.
	 *
	 * @return array Asset information.
	 */
	public function asset_config( string $filename, array $dependencies = [], $wp_enqueue_args = null ) : array {

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

		// Set the default args for wp_enqueue methods if none are provided.
		if ( null !== $wp_enqueue_args ) {
			$args = $wp_enqueue_args;
		} elseif ( $is_js ) {
			$args = false;
		} else {
			$args = 'all';
		}

		return [
			'url'          => $url,
			'dependencies' => $dependencies,
			'version'      => $version,
			'type'         => $is_js ? 'script' : 'style',
			'args'         => $args,
		];
	}


	/**
	 * Get path for file in plugin assets dist directory.
	 *
	 * @param string $file Asset file.
	 *
	 * @return string
	 */
	public function dist_path( string $file ) : string {
		if ( ! empty( $this->module_name ) ) {
			return path_join( $this->plugin_path, "assets/dist/$this->module_name/$file" );
		}

		return path_join( $this->plugin_path, "assets/dist/$file" );
	}

	/**
	 * Get path for file in plugin assets source directory.
	 *
	 * @param string $file Asset file.
	 *
	 * @return string
	 */
	public function src_path( string $file ) : string {
		if ( ! empty( $this->module_name ) ) {
			return path_join( $this->plugin_path, "modules/$this->module_name/assets/$file" );
		}

		return path_join( $this->plugin_path, "assets/$file" );
	}

	/**
	 * Construct public url for the file.
	 *
	 * @param string $filename The filename.
	 *
	 * @return string Public url for the file.
	 */
	public function asset_url( string $filename ) : string {
		if ( ! empty( $this->module_name ) ) {
			return rtrim( $this->plugin_url, '/' ) . "/assets/dist/$this->module_name/$filename";
		}

		return rtrim( $this->plugin_url, '/' ) . "/assets/dist/$filename";
	}

	/**
	 * Preload the given REST routes and pass data to Javascript.
	 *
	 * @param string[] $rest_routes REST routes to preload.
	 */
	public function preload_data( array $rest_routes ) {
		// Temporarily removes the user filter when loading from preload.
		remove_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );
		$preload_data = array_reduce(
			$rest_routes,
			'rest_preload_api_request',
			[]
		);
		add_action( 'pre_get_posts', [ Sensei()->teacher, 'filter_queries' ] );

		wp_add_inline_script(
			'wp-api-fetch',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_data ) ),
			'after'
		);

	}
}
