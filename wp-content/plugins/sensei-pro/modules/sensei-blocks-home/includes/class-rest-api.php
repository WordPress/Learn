<?php
/**
 * File containing the class Sensei_Interactive_Blocks_Sensei_Home\REST_API.
 *
 * @package sensei-blocks-home
 * @since   1.8.0
 */

namespace Sensei_Interactive_Blocks_Sensei_Home;

use Sensei_Interactive_Blocks_Sensei_Home\Providers\Help;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds the REST API endpoint for Sensei Home.
 *
 * @since 1.8.0
 */
class REST_API extends \WP_REST_Controller {
	const REST_NAMESPACE = 'sensei-internal/v1';
	const REST_BASE      = 'home';

	/**
	 * Help provider.
	 *
	 * @var Help
	 */
	private $help_provider;

	/**
	 * News provider.
	 *
	 * @var \Sensei_Home_News_Provider
	 */
	private $news_provider;

	/**
	 * Notices provider.
	 *
	 * @var \Sensei_Home_Notices_Provider
	 */
	private $notices_provider;

	/**
	 * Rest_Api constructor.
	 *
	 * @param Help                          $help_provider         Help provider.
	 * @param \Sensei_Home_News_Provider    $news_provider         News provider.
	 * @param \Sensei_Home_Notices_Provider $notices_provider      Notices provider.
	 */
	public function __construct(
		Help $help_provider,
		\Sensei_Home_News_Provider $news_provider,
		\Sensei_Home_Notices_Provider $notices_provider
	) {
		$this->help_provider    = $help_provider;
		$this->news_provider    = $news_provider;
		$this->notices_provider = $notices_provider;
	}

	/**
	 * Initialize the hooks.
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_rest_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_BASE,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_data' ],
					'permission_callback' => [ $this, 'can_user_access_rest_api' ],
				],
			]
		);
	}

	/**
	 * Check user permission for REST API access.
	 *
	 * @return bool Whether the user can access the Sensei Home REST API.
	 */
	public function can_user_access_rest_api() {
		return current_user_can( 'install_plugins' );
	}

	/**
	 * Get data for Sensei Home frontend.
	 *
	 * @return array Setup Home data
	 */
	public function get_data() {
		return [
			'help'    => $this->help_provider->get(),
			'news'    => $this->news_provider->get(),
			'notices' => $this->notices_provider->get(),
		];
	}
}
