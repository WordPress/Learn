<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Upgrade;
use WP_Syntex\Polylang_Pro\Updater\Updater;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang_Pro\Modules\Capabilities\User\Creator;

/**
 * A class to manage the Polylang Pro text domain and license key
 * and load all modules and integrations.
 *
 * @since 2.6
 */
class PLL_Pro {
	/**
	 * @var Updater|null
	 */
	public $updater;

	/**
	 * Constructor.
	 * Manages the compatibility with some plugins.
	 * It is loaded as soon as possible as we may need to act before other plugins are loaded.
	 *
	 * @since 2.6
	 */
	public function __construct() {
		// Loaded as soon as possible as we may need to act before other plugins are loaded.
		$this->load_integrations();

		Capabilities::set_user_creator( new Creator() );
	}

	/**
	 * Manages the Polylang Pro translations and license key.
	 * Loads the modules.
	 *
	 * @since 2.8
	 *
	 * @param PLL_Base $polylang Polylang object.
	 * @return void
	 */
	public function init( &$polylang ) {
		/** @var \WP_Syntex\Polylang\Options\Options $options */
		$options = $polylang->options;
		add_action( 'pll_upgrade', array( new Upgrade( $options ), 'upgrade' ) );

		if ( $polylang instanceof PLL_Admin_Base ) {
			$this->updater = new Updater( POLYLANG_PRO_FILE, 'Polylang Pro', POLYLANG_VERSION, 'polylang-pro' );

			// Download Polylang language packs.
			add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 ); // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_args
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );
		}

		// Prolylang Pro is equivalent to Polylang for plugin dependencies.
		add_filter( 'wp_plugin_dependencies_slug', array( $this, 'convert_plugin_dependency' ) );

		// Loads the modules.
		$this->load_modules( $polylang );
	}

	/**
	 * Hack to download Polylang languages packs
	 *
	 * @since 1.9
	 *
	 * @param array  $args HTTP request args.
	 * @param string $url  The url of the request.
	 * @return array
	 */
	public function http_request_args( $args, $url ) {
		if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/' ) ) {
			$plugins = (array) json_decode( $args['body']['plugins'], true );
			if ( empty( $plugins['plugins']['polylang/polylang.php'] ) ) {
				$plugins['plugins']['polylang/polylang.php'] = array( 'Version' => POLYLANG_VERSION );
				$args['body']['plugins'] = wp_json_encode( $plugins );
			}
		}
		return $args;
	}

	/**
	 * Remove Polylang from the list of plugins to update if it is not installed
	 *
	 * @since 2.1.1
	 *
	 * @param stdClass $value The value stored in the update_plugins site transient.
	 * @return stdClass
	 */
	public function pre_set_site_transient_update_plugins( $value ) {
		// We encountered a 3rd party plugin setting the transient before the function get_plugins() is available.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		if ( isset( $value->response ) ) {
			if ( empty( $plugins['polylang/polylang.php'] ) ) {
				unset( $value->response['polylang/polylang.php'] );
			} elseif ( isset( $value->response['polylang/polylang.php']->new_version ) && $plugins['polylang/polylang.php']['Version'] === $value->response['polylang/polylang.php']->new_version ) {
				$value->no_update['polylang/polylang.php'] = $value->response['polylang/polylang.php'];
				unset( $value->response['polylang/polylang.php'] );
			}
		}
		return $value;
	}

	/**
	 * Converts the Polylang plugin slug to Polylang Pro for plugin dependencies.
	 *
	 * This allows plugins requiring Polylang to work with Polylang Pro too.
	 *
	 * @since 3.7
	 *
	 * @param string $slug The plugin slug.
	 * @return string
	 */
	public function convert_plugin_dependency( $slug ): string {
		return 'polylang' === $slug ? dirname( POLYLANG_BASENAME ) : (string) $slug;
	}

	/**
	 * Loads the compatibility with some plugins and themes.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	private function load_integrations(): void {
		if ( defined( 'PLL_PLUGINS_COMPAT' ) && ! PLL_PLUGINS_COMPAT ) {
			return;
		}

		$load_scripts = require POLYLANG_PRO_DIR . '/src/integrations/integration-build.php';

		foreach ( $load_scripts as $load_script ) {
			require_once POLYLANG_PRO_DIR . "/src/integrations/{$load_script}/load.php";
		}
	}

	/**
	 * Loads the modules.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Base $polylang Polylang object.
	 * @return void
	 */
	private function load_modules( $polylang ): void {
		$load_scripts = require POLYLANG_PRO_DIR . '/src/modules/module-build.php';

		foreach ( $load_scripts as $load_script ) {
			require_once POLYLANG_PRO_DIR . "/src/modules/{$load_script}/load.php";
		}
	}
}
