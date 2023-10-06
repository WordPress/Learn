<?php
/**
 * File contains AI_API_Client class.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro;

use Sensei_Compat_Admin;
use Sensei_Utils;
use SenseiLMS_Licensing\License_Manager;
use WC_Helper;
use WC_Helper_Options;
use WP_Error;

/**
 * AI_API_Client class.
 *
 * Responsible for making requests to the AI API located at senseilms.com.
 *
 * @internal
 * @since 1.16.0
 */
class AI_API_Client {
	/**
	 * Whether to check for a Sensei Interactive Blocks license.
	 *
	 * @var bool
	 */
	private $should_check_ib_license = false;

	/**
	 * Make a request to the AI API.
	 *
	 * @internal
	 * @since 1.16.0
	 *
	 * @param string $route The URL for the route.
	 * @param array  $params The payload parameters for the request.
	 *
	 * @return array|WP_Error
	 */
	public function request( string $route, array $params = [] ) {
		$license_params = $this->get_license_parameter();

		if ( null === $license_params ) {
			return new WP_Error( 400, __( 'No valid license was found. Verify you have a license and it\'s still active.', 'sensei-pro' ) );
		}

		$payload = array_merge(
			$params,
			$license_params
		);

		$response = wp_remote_post(
			$this->get_base_api_url() . '/' . $route,
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 60,
				'body'    => wp_json_encode( $payload ),
			]
		);

		// Check generic request error.
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 500, __( 'Something went wrong. Please try again.', 'sensei-pro' ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		// Check rate limit error.
		if ( 429 === $response_code ) {
			return new WP_Error( 429, __( 'You have temporarily exceeded the amount of requests. Please try again in a while.', 'sensei-pro' ) );
		}

		// Check licensing.
		if ( 401 === $response_code ) {
			return new WP_Error( 401, __( 'Invalid license. You need an active and valid license.', 'sensei-pro' ) );
		}

		// Catch all remaining errors.
		if ( 200 !== $response_code ) {
			return new WP_Error( 500, __( 'Something went wrong. Please try again.', 'sensei-pro' ) );
		}

		return json_decode(
			wp_remote_retrieve_body( $response ),
			true
		);
	}

	/**
	 * Set whether to check for a Sensei Interactive Blocks license.
	 *
	 * @internal
	 * @since 1.16.0
	 *
	 * @param bool $check Whether to check for a Sensei Interactive Blocks license.
	 */
	public function set_should_check_ib_license( bool $check ): void {
		$this->should_check_ib_license = $check;
	}

	/**
	 * Returns the license parameter for the SenseiLMS.com API.
	 *
	 * @return array|null The license parameter to pass to the API, or null if no license data was found.
	 */
	private function get_license_parameter() {
		if ( defined( 'SENSEI_COMPAT_PLUGIN' ) && SENSEI_COMPAT_PLUGIN ) {
			$license = $this->get_woothemes_sensei_license();
		} else {
			$license = $this->get_standalone_license( 'sensei-pro' );
			if ( $this->should_check_ib_license && null === $license ) {
				$license = $this->get_standalone_license( 'sensei-interactive-blocks' );
			}
		}

		return $license;
	}

	/**
	 * Get license data associated with sensei-pro or with sensei-interactive-blocks.
	 *
	 * @param string $plugin_slug The plugin slug to get the license data for.
	 * @return array|null The license data if any is available, or null if no data was found.
	 */
	private function get_standalone_license( $plugin_slug ) {
		$license = License_Manager::get_license_status( $plugin_slug );
		if ( ! $license['is_valid'] ) {
			// License data is not valid, return early.
			return null;
		}

		return [
			'license_type' => 'senseilmscom',
			'license_key'  => $license['license_key'],
			'site_url'     => network_site_url(),
		];
	}

	/**
	 * Get license data associated with woothemes-sensei using WooCommerce's API.
	 *
	 * @return array|null The license data if any is available, or null if no data was found.
	 */
	private function get_woothemes_sensei_license() {
		if ( ! Sensei_Utils::is_woocommerce_active() || ! class_exists( '\Sensei_Compat_Admin' ) ) {
			// WooCommerce is not active, or this isn't woothemes-sensei, return early.
			return null;
		}
		$subscriptions = WC_Helper::get_subscriptions();
		$auth          = WC_Helper_Options::get( 'auth' );
		if ( ! $auth || empty( $subscriptions ) ) {
			return null;
		}

		$site_id_str = $auth['site_id'];
		$site_id_int = absint( $site_id_str );
		foreach ( $subscriptions as $subscription ) {
			$connections = array_key_exists( 'connections', $subscription ) ? $subscription['connections'] : [];
			$in_site     = in_array( $site_id_str, $connections, true );
			$in_site     = $in_site || in_array( $site_id_int, $connections, true );
			if ( Sensei_Compat_Admin::WOO_PRODUCT_ID === $subscription['product_id'] && $in_site ) {
				// subscription for woothemes-sensei found, return the license data.
				return [
					'license_type' => 'wccom',
					'license_key'  => $subscription['product_key'],
					'site_url'     => get_home_url(),
				];
			}
		}

		return null;
	}

	/**
	 * Get the base API URL.
	 *
	 * @return string
	 */
	private function get_base_api_url(): string {
		return License_Manager::get_api_url() . '/senseilms-ai/v1';
	}
}
