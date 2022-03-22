<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Sensei_Pro_Setup\Rest_API.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro_Setup;

use SenseiLMS_Licensing\License_Manager;

/**
 * REST API class for activating the plugin license and installing Sensei Core.
 */
class Rest_Api extends \WP_REST_Controller {

	/**
	 * Endpoint namespace for internal routes.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-internal/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'sensei-pro-setup';

	/**
	 * Setup context.
	 *
	 * @var Setup_Context;
	 */
	protected $setup_context;

	/**
	 * The constructor.
	 *
	 * @param Setup_Context $setup_context
	 */
	public function __construct( Setup_Context $setup_context ) {
		$this->setup_context = $setup_context;
	}

	/**
	 * Register the routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/activate-license",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'activate_license' ],
					'permission_callback' => [ $this, 'check_user_can_install_plugins' ],
					'args'                => [
						'license_key' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/install-sensei",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'install_sensei' ],
					'permission_callback' => [ $this, 'check_user_can_install_plugins' ],
				],
			]
		);
	}

	/**
	 * Activate the license for the given license key and plugin slug.
	 *
	 * @param WP_REST_Request $request The current request.
	 */
	public function activate_license( $request ) {
		$license_key  = $request->get_param( 'license_key' );
		$api_response = License_Manager::activate_license(
			$this->setup_context->plugin_slug,
			$license_key
		);

		// Pass through the API response from the license server.
		return rest_ensure_response( $api_response );
	}

	/**
	 * Installs the Sensei Core plugin.
	 */
	public function install_sensei() {
		try {
			$this->install_plugin( Wizard::SENSEI_PLUGIN_SLUG );
			wp_cache_delete( 'plugins', 'plugins' );
			$activate_url = Wizard::get_sensei_activate_url();
			return rest_ensure_response(
				[
					'success'             => true,
					'activate_sensei_url' => $activate_url,
				]
			);
		} catch ( \Exception $error ) {
			return rest_ensure_response(
				[
					'success' => false,
					'error'   => $error,
				]
			);
		}
	}

	/**
	 * Check if the current user has permission to activate a license
	 * for the given plugin.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_user_can_install_plugins() {
		return current_user_can( 'install_plugins' );
	}

	/**
	 * Install plugin.
	 *
	 * @param string $plugin_slug Plugin slug.
	 *
	 * @throws \Exception When there is an installation error.
	 */
	public function install_plugin( $plugin_slug ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem();

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \WP_Upgrader( $skin );

		$plugin_information = plugins_api(
			'plugin_information',
			[
				'slug'   => $plugin_slug,
				'fields' => [
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'homepage'          => false,
					'donate_link'       => false,
					'author_profile'    => false,
					'author'            => false,
				],
			]
		);

		if ( is_wp_error( $plugin_information ) ) {
			throw new \Exception( $this->get_error_message( $plugin_information ) );
		}

		// Suppress feedback.
		ob_start();

		$package  = $plugin_information->download_link;
		$download = $upgrader->download_package( $package );

		if ( is_wp_error( $download ) ) {
			throw new \Exception( $this->get_error_message( $download ) );
		}

		$working_dir = $upgrader->unpack_package( $download, true );

		if ( is_wp_error( $working_dir ) ) {
			throw new \Exception( $this->get_error_message( $working_dir ) );
		}

		$result = $upgrader->install_package(
			[
				'source'                      => $working_dir,
				'destination'                 => WP_PLUGIN_DIR,
				'clear_destination'           => false,
				'abort_if_destination_exists' => false,
				'clear_working'               => true,
				'hook_extra'                  => [
					'type'   => 'plugin',
					'action' => 'install',
				],
			]
		);

		// Discard feedback.
		ob_end_clean();

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $this->get_error_message( $result ) );
		}

		return $result;
	}

	/**
	 * Activate plugin.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @throws \Exception When there is an activation error.
	 */
	public function activate_plugin( $plugin_file ) {
		$result = activate_plugin( $plugin_file, '', false, true );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $this->get_error_message( $result ) );
		}

		return $result;
	}

	/**
	 * Wrapper to get error message and give the `get_error_data` as fallback.
	 *
	 * @param \WP_Error $error
	 *
	 * @return string Error message.
	 */
	private function get_error_message( $error ) {
		if ( $error->get_error_message() ) {
			return $error->get_error_message();
		}

		return $error->get_error_data();
	}
}
