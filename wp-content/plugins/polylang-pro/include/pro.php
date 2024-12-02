<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to manage the Polylang Pro text domain and license key
 * and load all modules and integrations.
 *
 * @since 2.6
 */
class PLL_Pro {

	/**
	 * Constructor.
	 * Manages the compatibility with some plugins.
	 * It is loaded as soon as possible as we may need to act before other plugins are loaded.
	 *
	 * @since 2.6
	 */
	public function __construct() {
		require_once __DIR__ . '/functions.php';

		$load_scripts = glob( POLYLANG_PRO_DIR . '/integrations/*/load.php', GLOB_NOSORT );
		if ( is_array( $load_scripts ) ) {
			foreach ( $load_scripts as $load_script ) {
				require_once $load_script; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			}
		}
	}

	/**
	 * Manages the Polylang Pro translations and license key.
	 * Loads the modules.
	 *
	 * @since 2.8
	 *
	 * @param object $polylang Polylang object.
	 * @return void
	 */
	public function init( &$polylang ) {
		if ( $polylang instanceof PLL_Admin_Base ) {
			new PLL_License( POLYLANG_PRO_FILE, 'Polylang Pro', POLYLANG_VERSION, 'WP SYNTEX' );
			new PLL_T15S( 'polylang-pro', 'https://packages.translationspress.com/wp-syntex/polylang-pro/packages.json' );

			// Download Polylang language packs.
			add_filter( 'http_request_args', array( $this, 'http_request_args' ), 10, 2 ); // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_args
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );
		}

		// Loads the modules.
		$load_scripts = glob( POLYLANG_PRO_DIR . '/modules/*/load.php', GLOB_NOSORT );
		if ( is_array( $load_scripts ) ) {
			foreach ( $load_scripts as $load_script ) {
				require_once $load_script; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			}
		}
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
}
