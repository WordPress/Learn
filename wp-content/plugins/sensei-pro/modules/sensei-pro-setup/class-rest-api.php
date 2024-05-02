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
	 * An instance of this class.
	 *
	 * @var self;
	 */
	private static $instance;

	/**
	 * The constructor.
	 */
	private function __construct() {
	}

	/**
	 * Initializes this class.
	 */
	public function init() {
		self::instance();
	}

	/**
	 * Retrieves the instance of this class.
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
					'permission_callback' => [ $this, 'check_user_is_administrator' ],
					'args'                => [
						'license_key' => [
							'type'     => 'string',
							'required' => true,
						],
						'plugin_slug' => [
							'type'     => 'string',
							'required' => true,
						],
						'nonce'       => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/deactivate-license",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'deactivate_license' ],
					'permission_callback' => [ $this, 'check_user_is_administrator' ],
					'args'                => [
						'license_key' => [
							'type'     => 'string',
							'required' => true,
						],
						'plugin_slug' => [
							'type'     => 'string',
							'required' => true,
						],
						'nonce'       => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/flush-wpcom-license",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'flush_wpcom_license' ],
					'permission_callback' => [ $this, 'check_user_is_administrator' ],
					'args'                => [
						'plugin_slug' => [
							'type'     => 'string',
							'required' => true,
						],
						'nonce'       => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/receive-wpcom-license-key",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'receive_wpcom_license_key' ],
					'permission_callback' => '__return_true',
					'args'                => [
						'plugin_slug'  => [
							'type'     => 'string',
							'required' => true,
						],
						'license_key'  => [
							'type'     => 'string',
							'required' => true,
						],
						'custom_nonce' => [
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
	 *
	 * @return WP_REST_Response
	 */
	public function activate_license( $request ) {
		$license_key  = sanitize_text_field( $request->get_param( 'license_key' ) );
		$plugin_slug  = sanitize_text_field( $request->get_param( 'plugin_slug' ) );
		$nonce        = $request->get_param( 'nonce' );
		$api_response = false;

		if ( wp_verify_nonce( $nonce, 'license-form-' . $plugin_slug ) ) {
			$api_response = License_Manager::activate_license(
				$plugin_slug,
				$license_key
			);
		}

		if ( false === $api_response ) {
			$api_response = [
				'success' => false,
				'message' => __( 'An error occurred while activating the license. Please reload the page and try again.', 'sensei-pro' ),
			];
		}

		// Pass through the API response from the license server.
		return rest_ensure_response( $api_response );
	}

	/**
	 * Deactivate the license for the given license key and plugin slug.
	 *
	 * @param WP_REST_Request $request The current request.
	 *
	 * @return WP_REST_Response
	 */
	public function deactivate_license( $request ) {
		$license_key  = sanitize_text_field( $request->get_param( 'license_key' ) );
		$plugin_slug  = sanitize_text_field( $request->get_param( 'plugin_slug' ) );
		$nonce        = $request->get_param( 'nonce' );
		$api_response = false;

		if ( wp_verify_nonce( $nonce, 'license-form-' . $plugin_slug ) ) {
			$api_response = License_Manager::deactivate_license(
				$plugin_slug,
				$license_key
			);
		}

		if ( false === $api_response ) {
			$api_response = [
				'success' => false,
				'message' => __( 'An error occurred while deactivating the license. Please reload the page and try again.', 'sensei-pro' ),
			];
		}

		// Pass through the API response from the license server.
		return rest_ensure_response( $api_response );
	}

	/**
	 * Creates a custom nonce for the given plugin slug.
	 *
	 * @param string $action The action name.
	 *
	 * @return string The custom nonce.
	 */
	private function create_custom_nonce( $action ) {
		$custom_nonce = wp_generate_password( 15, false );
		set_transient( 'sensei-pro-custom-nonce-' . $action, $custom_nonce, 60 );

		return $custom_nonce;
	}

	/**
	 * Checks if the given nonce is valid for the given action.
	 *
	 * @param string $nonce  The nonce to check.
	 * @param string $action The action name.
	 *
	 * @return bool True if the nonce is valid, false otherwise.
	 */
	private function check_custom_nonce( $nonce, $action ) {
		$saved_nonce = get_transient( 'sensei-pro-custom-nonce-' . $action );

		return ! empty( $saved_nonce ) && $nonce === $saved_nonce;
	}

	/**
	 * Flushes the license key for the given plugin slug.
	 * It's used to fetch and save the license key for the WPCOM website when it doesn't work on the normal flow.
	 *
	 * @param WP_REST_Request $request The current request.
	 *
	 * @return WP_REST_Response
	 */
	public function flush_wpcom_license( $request ) {
		$plugin_slug  = sanitize_text_field( $request->get_param( 'plugin_slug' ) );
		$nonce        = $request->get_param( 'nonce' );
		$api_response = false;

		if ( wp_verify_nonce( $nonce, 'license-form-' . $plugin_slug ) ) {
			// Generate a custom nonce for the external request.
			// We don't use the original nonce system beucase it's not reliable for authentication.
			$custom_nonce = $this->create_custom_nonce( 'receive-license-' . $plugin_slug );

			$activation_url = add_query_arg(
				'custom_nonce',
				$custom_nonce,
				get_rest_url( get_current_blog_id(), '/sensei-pro-internal/v1/sensei-pro-setup/receive-wpcom-license-key' )
			);
			$api_response   = License_Manager::flush_wpcom_license( $plugin_slug, $activation_url );
		}

		if ( false === $api_response ) {
			$api_response = [
				'success' => false,
				'message' => __( 'An error occurred while activating the license. Please reload the page and try again.', 'sensei-pro' ),
			];
		}

		// Pass through the API response from the license server.
		return rest_ensure_response( $api_response );
	}

	/**
	 * Receives the license key for the given plugin slug from the WPCOM website.
	 * This endpoint is expected to be called as the `activation_url` from flush WPCOM license.
	 *
	 * @param WP_REST_Request $request The current request.
	 *
	 * @return WP_REST_Response
	 */
	public function receive_wpcom_license_key( $request ) {
		$license_key  = sanitize_text_field( $request->get_param( 'license_key' ) );
		$plugin_slug  = sanitize_text_field( $request->get_param( 'plugin_slug' ) );
		$custom_nonce = $request->get_param( 'custom_nonce' );
		$response     = false;

		if ( $this->check_custom_nonce( $custom_nonce, 'receive-license-' . $plugin_slug ) ) {
			License_Manager::receive_wpcom_license_key( $plugin_slug, $license_key );
			$response = [ 'success' => true ];
		}

		if ( false === $response ) {
			$response = [
				'success' => false,
				'message' => __( 'An error occurred while receiving the license key.', 'sensei-pro' ),
			];
		}

		// Pass through the API response from the license server.
		return rest_ensure_response( $response );
	}

	/**
	 * Installs the Sensei Core plugin.
	 *
	 * @return WP_REST_Response
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
	 * Check if the current user has permission to install plugins.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_user_can_install_plugins() {
		return current_user_can( 'install_plugins' );
	}

	/**
	 * Check if current user is an administrator.
	 *
	 * @return bool|\WP_Error
	 */
	public function check_user_is_administrator() {
		return current_user_can( 'manage_options' );
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
			throw new \Exception( esc_html( $this->get_error_message( $plugin_information ) ) );
		}

		// Suppress feedback.
		ob_start();

		$package  = $plugin_information->download_link;
		$download = $upgrader->download_package( $package );

		if ( is_wp_error( $download ) ) {
			throw new \Exception( esc_html( $this->get_error_message( $download ) ) );
		}

		$working_dir = $upgrader->unpack_package( $download, true );

		if ( is_wp_error( $working_dir ) ) {
			throw new \Exception( esc_html( $this->get_error_message( $working_dir ) ) );
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
			throw new \Exception( esc_html( $this->get_error_message( $result ) ) );
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
			throw new \Exception( esc_html( $this->get_error_message( $result ) ) );
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
