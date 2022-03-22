<?php
/**
 * File containing the class SenseiLMS_Licensing\License_Manager.
 *
 * @package senseilms-licensing
 * @since   1.0.0
 */

namespace SenseiLMS_Licensing;

/**
 * Main entrypoint for the licensing from SenseiLMS Store.
 *
 * @since 1.0.0
 */
class License_Manager {

	const LICENSE_KEY_OPTION_PREFIX = 'senseilms_license_key__';

	const API_URL = 'https://senseilms.com/wp-json';

	// Transient configuration.
	const CACHE_KEY_PREFIX = 'senseilms_licensing_info__';
	const CACHE_TTL        = 3600; // Timeout in seconds.

	/**
	 * Singleton instance.
	 *
	 * @var License_Manager
	 */
	private static $instance;

	/**
	 * Private constructor.
	 */
	private function __construct() {
		// Silence.
	}

	/**
	 * Initialize the plugin updater for the given plugin details.
	 *
	 * @param string $main_plugin_file Plugin's main file path. Recommended to use `__FILE__` from the main plugin class itself.
	 * @param string $version          Plugin version.
	 */
	public static function init( $main_plugin_file, $version ) {
		self::include_dependencies();

		SenseiLMS_Plugin_Updater::init( $main_plugin_file, $version );
		License_Notice::init( $main_plugin_file );
	}

	/**
	 * Include all required files.
	 */
	private static function include_dependencies() {
		require_once dirname( __FILE__ ) . '/class-senseilms-plugin-updater.php';
		require_once dirname( __FILE__ ) . '/class-license-notice.php';
	}

	/**
	 * Returns the license key currently being used and its status.
	 *
	 * @param string $plugin_slug The plugin slug.
	 *
	 * @return array The output is an associative array containing keys:
	 * - `license_key`: (string|null) The License Key that is persisted in the system. A null value means that license has not been set.
	 * - `is_valid`: (bool|null) Whether the license is valid for the current installation and plugin or not. A null value means check could not be done or is not available yet.
	 * - `domain`: (string) The current domain sanitized and in lowercase.
	 */
	public static function get_license_status( $plugin_slug ) {
		// Initially we don't know.
		$is_license_valid = null;
		// Get domain.
		$domain = self::get_domain();
		// Get license from option.
		$license_key = get_option( self::LICENSE_KEY_OPTION_PREFIX . $plugin_slug, null );
		if ( ! is_null( $license_key ) ) {
			// Get response from cache.
			$remote = get_transient( self::CACHE_KEY_PREFIX . $plugin_slug );
			if ( false === $remote ) {
				// Call licensing service if no cached response available.
				$url    = add_query_arg(
					[
						'license_key' => $license_key,
						'plugin_slug' => $plugin_slug,
						'domain'      => $domain,
						'ts'          => time(), // Adding some timestamp to workaround cache issues.
					],
					self::get_api_url() . '/licensing/v1/info'
				);
				$remote = wp_remote_get( $url, [ 'headers' => [ 'Cache-Control' => 'no-cache' ] ] );

				// Cache any response.
				set_transient( self::CACHE_KEY_PREFIX . $plugin_slug, $remote, self::CACHE_TTL );
			}

			// Process response (from remote or cached).
			if (
				! is_wp_error( $remote )
				&& 200 === wp_remote_retrieve_response_code( $remote )
				&& ! empty( wp_remote_retrieve_body( $remote ) )
			) {
				$response = json_decode( wp_remote_retrieve_body( $remote ) );
				if ( isset( $response->is_valid ) ) {
					$is_license_valid = $response->is_valid;
				}
			}
		}

		// Return output.
		return [
			'license_key' => $license_key,
			'is_valid'    => $is_license_valid,
			'domain'      => $domain,
		];
	}

	/**
	 * Tries to activate the given license for the current plugin and domain and stores it if successful.
	 *
	 * @param string $plugin_slug  The plugin slug.
	 * @param string $license_key  The license key given by the user.
	 *
	 * @return array|false Pass through the API response from the licensing server, or return false on error.
	 */
	public static function activate_license( $plugin_slug, $license_key ) {
		// Assume error unless told otherwise.
		$response = false;
		// Get domain.
		$domain = self::get_domain();
		// Call activation service.
		$remote = wp_remote_post(
			self::get_api_url() . '/licensing/v1/activate',
			[
				'body' => [
					'license_key' => $license_key,
					'plugin_slug' => $plugin_slug,
					'domain'      => $domain,
				],
			]
		);
		if (
			! is_wp_error( $remote )
			&& 200 === wp_remote_retrieve_response_code( $remote )
			&& ! empty( wp_remote_retrieve_body( $remote ) )
		) {
			$response = json_decode( wp_remote_retrieve_body( $remote ) );
			if ( isset( $response->success ) ) {
				if ( $response->success ) {
					update_option( self::LICENSE_KEY_OPTION_PREFIX . $plugin_slug, $license_key );
				}
			}
		}

		// Flush cache no matter the response itself, so we have a mechanism to flush it on purpose.
		delete_transient( self::CACHE_KEY_PREFIX . $plugin_slug );

		return $response;
	}

	/**
	 * Returns the API URL.
	 *
	 * @return string
	 */
	public static function get_api_url() {
		// @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		return apply_filters( 'senseilms_licensing_api', self::API_URL );
	}

	/**
	 * Returns the current domain sanitized and in lowercase.
	 *
	 * @return string
	 */
	private static function get_domain() {
		$urlparts = wp_parse_url( home_url() );
		$domain   = $urlparts['host'];

		return strtolower( sanitize_text_field( $domain ) );
	}
}
