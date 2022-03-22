<?php
/**
 * File containing the class SenseiLMS_Licensing\SenseiLMS_Plugin_Updater.
 *
 * @package senseilms-licensing
 * @since   1.0.0
 */

namespace SenseiLMS_Licensing;

use stdClass;
use WP_Error;

/**
 * Manages plugin updates by hooking into WordPress's plugins update system and querying SenseiLMS Store's API.
 *
 * @since 1.0.0
 */
class SenseiLMS_Plugin_Updater {

	const CACHE_KEY_PREFIX = 'senseilms_plugin_updater_info__';
	const CACHE_TTL        = 3600;

	/**
	 * Full qualified name for the plugin. Relative path from plugins directory. Example: 'akismet/akismet.php'.
	 *
	 * @var string
	 */
	private $plugin_full_name;

	/**
	 * Plugin slug name. Example: 'akismet'.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Current version for the plugin. Example: '1.0.0'.
	 *
	 * @var string
	 */
	private $version;


	/**
	 * Private constructor.
	 *
	 * @param string $main_plugin_file_absolute_path Plugin's main file path.
	 * @param string $version                        Plugin version.
	 */
	private function __construct( $main_plugin_file_absolute_path, $version ) {
		$this->plugin_full_name = plugin_basename( $main_plugin_file_absolute_path );
		$this->plugin_slug      = basename( $main_plugin_file_absolute_path, '.php' );
		$this->version          = $version;
	}

	/**
	 * Initialize the plugin updater for the given plugin details.
	 *
	 * @param string $main_plugin_file Plugin's main file path. Recommended to use `__FILE__` from the main plugin class itself.
	 * @param string $version          Plugin version.
	 */
	public static function init( $main_plugin_file, $version ) {
		$instance = new self( $main_plugin_file, $version );

		add_filter( 'plugins_api', [ $instance, 'get_plugin_info' ], 10, 3 );
		add_filter( 'site_transient_update_plugins', [ $instance, 'maybe_inject_custom_update_to_update_plugins_transient' ] );
	}

	/**
	 * Get plugin information as expected by the `plugins_api` hook.
	 * This will be called to display the details for the updated in the detailed view.
	 *
	 * @param false|object|array $res    Result. As defined per the `plugins_api` hook.
	 * @param string             $action The action being executed. As defined per the `plugins_api` hook.
	 * @param object             $args   The arguments. As defined per the `plugins_api` hook.
	 *
	 * @hooked plugins_api
	 *
	 * @return false|object If other than false is returned the actual call to wordpress.org is not done.
	 */
	public function get_plugin_info( $res, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return false;
		}
		if ( $this->plugin_slug !== $args->slug ) {
			return false;
		}

		$remote = $this->request_info();
		if ( is_wp_error( $remote ) ) {
			// Early return in case request to SenseiLMS.com failed.
			return false;
		}

		$res                = new stdClass();
		$res->name          = $remote->name;
		$res->slug          = $remote->slug;
		$res->author        = $remote->author;
		$res->version       = $remote->version;
		$res->requires      = $remote->requires;
		$res->tested        = $remote->tested;
		$res->requires_php  = $remote->requires_php;
		$res->last_updated  = $remote->last_updated;
		$res->sections      = [
			'description'  => $remote->sections->description,
			'installation' => $remote->sections->installation,
			'changelog'    => $remote->sections->changelog,
		];
		$res->download_link = $remote->download_url;

		return $res;
	}

	/**
	 * Potentially injects the details for a new plugin version by checking against the remote server.
	 * This is done by hooking into the `update_plugins` transient by using the `site_transient_update_plugins` hook.
	 *
	 * @param mixed $transient The plugin_update transient.
	 *
	 * @hooked site_transient_update_plugins See reference for `site_transient_transient`.
	 *
	 * @return mixed
	 */
	public function maybe_inject_custom_update_to_update_plugins_transient( $transient ) {

		// Skip empty transients.
		if ( empty( $transient ) ) {
			return $transient;
		}

		$remote = $this->request_info();
		if ( is_wp_error( $remote ) ) {
			// Request failed so do not inject anything into the transient.
			return $transient;
		}

		if (
			$remote
			&& version_compare( $this->version, $remote->version, '<' )
			&& version_compare( get_bloginfo( 'version' ), $remote->requires, '>=' )
			&& version_compare( PHP_VERSION, $remote->requires_php, '>=' )
		) {

			$res                                 = new stdClass();
			$res->slug                           = $remote->slug;
			$res->plugin                         = $this->plugin_full_name;
			$res->new_version                    = $remote->version;
			$res->tested                         = $remote->tested;
			$res->package                        = $remote->download_url;
			$transient->response[ $res->plugin ] = $res;
		}

		return $transient;
	}

	/**
	 * Helper function that retrieves the latest version information from the remote server if there is a valid license in the system.
	 * This function caches remote response by using transients.
	 *
	 * @return array|WP_Error
	 */
	private function request_info() {
		$license_status = License_Manager::get_license_status( $this->plugin_slug );
		if ( true !== $license_status['is_valid'] ) {
			// Early return in case the license is not set or is invalid.
			// Checking if a license is valid might generate a request itself to the server but those should be cached too.
			return new WP_Error( 'invalid-license', __( 'The license is not valid so checking for updates is disabled.', 'sensei-pro' ) );
		}

		$cache_key = self::CACHE_KEY_PREFIX . $this->plugin_slug;
		$remote    = get_transient( $cache_key );

		if ( false === $remote ) {
			$remote = wp_remote_get(
				add_query_arg(
					[
						'plugin_slug' => $this->plugin_slug,
						'license_key' => $license_status['license_key'],
						'domain'      => $license_status['domain'],
						'ts'          => time(), // Adding some timestamp to workaround cache issues.
					],
					License_Manager::get_api_url() . '/plugin-updater/v1/info'
				),
				[
					'timeout' => 10,
					'headers' => [
						'Accept'        => 'application/json',
						'Cache-Control' => 'no-cache',
					],
				]
			);

			// Caching any response.
			set_transient( $cache_key, $remote, self::CACHE_TTL );
		}

		// Check response for errors.
		if (
			is_wp_error( $remote )
			|| 200 !== wp_remote_retrieve_response_code( $remote )
			|| empty( wp_remote_retrieve_body( $remote ) )
		) {
			return new WP_Error( 'remote-error', __( 'Remote answered with an error.', 'sensei-pro' ) );
		}

		// Check response for valid json.
		$response = json_decode( wp_remote_retrieve_body( $remote ) );
		if ( is_null( $response ) ) {
			return new WP_Error( 'invalid-remote-response', __( 'Remote answered with an invalid response.', 'sensei-pro' ) );
		}

		return $response;
	}
}
