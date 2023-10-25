<?php
/**
 * File contains Tutor_Chat_Service class.
 *
 * @package sensei-interactive-blocks
 */

namespace Sensei_Pro_Interactive_Blocks\Tutor_Chat;

use SenseiLMS_Licensing\License_Manager;

/**
 * Tutor_Chat_Service class.
 *
 * @since 1.15.1
 */
class Tutor_Chat_Service {
	/**
	 * Request remote endpoint to get clue from the AI Tutor.
	 *
	 * @param string $question The question.
	 * @param string $answer The correct answer to the question.
	 * @param string $context The context of the question.
	 * @param string $student_reply The student's reply to the question.
	 * @return array Status and response body.
	 * @throws \Exception When something goes wrong.
	 */
	public function request( $question, $answer, $context, $student_reply ) {
		$license_params = $this->get_license_parameter();

		if ( null === $license_params ) {
			return $this->create_response( 400, __( 'No valid license was found. Verify you have a license and it\'s still active.', 'sensei-pro' ) );
		}

		$payload = array_merge(
			[
				'question'      => $question,
				'answer'        => $answer,
				'context'       => $context,
				'student_reply' => $student_reply,
			],
			$license_params
		);

		$headers = [
			'content-Type' => 'application/json',
		];

		$response = wp_remote_post(
			$this->get_tutor_chat_api_endpoint(),
			[
				'method'  => 'POST',
				'headers' => $headers,
				'timeout' => 60,
				'body'    => wp_json_encode( $payload ),
			]
		);

		// Check generic request error.
		if ( is_wp_error( $response ) ) {
			return $this->create_response( 500, __( 'Something went wrong', 'sensei-pro' ) . ': ' . $response->get_error_message() );
		}

		$http_response_code = wp_remote_retrieve_response_code( $response );

		// Check rate limit error.
		if ( 429 === $http_response_code ) {
			return $this->create_response( 429, __( 'You have temporarily exceeded the amount of requests. Please try again in a while.', 'sensei-pro' ) );
		}

		// Check licensing.
		if ( 401 === $http_response_code ) {
			return $this->create_response( 401, __( 'Invalid license. You need an active and valid license.', 'sensei-pro' ) );
		}

		// Catch all remaining errors.
		if ( 200 !== $http_response_code ) {
			return $this->create_response( 500, __( 'Something went wrong. If the error persists try changing params slightly.', 'sensei-pro' ) );
		}

		// Handle successful response body.
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Unexpected format.
		if ( ! isset( $response_body['student_correct'] ) || ! isset( $response_body['tutor_response'] ) ) {
			return $this->create_response( 500, __( 'Unexpected response from server. Try again later.', 'sensei-pro' ) );
		}

		// Successful response.
		return $this->create_response( 200, $response_body );
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
			if ( null === $license ) {
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
		if ( ! \Sensei_Utils::is_woocommerce_active() || ! class_exists( '\Sensei_Compat_Admin' ) ) {
			// WooCommerce is not active, or this isn't woothemes-sensei, return early.
			return null;
		}
		$subscriptions = \WC_Helper::get_subscriptions();
		$auth          = \WC_Helper_Options::get( 'auth' );
		if ( ! $auth || empty( $subscriptions ) ) {
			return null;
		}

		$site_id_str = $auth['site_id'];
		$site_id_int = absint( $site_id_str );
		foreach ( $subscriptions as $subscription ) {
			$connections = array_key_exists( 'connections', $subscription ) ? $subscription['connections'] : [];
			$in_site     = in_array( $site_id_str, $connections, true );
			$in_site     = $in_site || in_array( $site_id_int, $connections, true );
			if ( \Sensei_Compat_Admin::WOO_PRODUCT_ID === $subscription['product_id'] && $in_site ) {
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
	 * Get the API endpoint for making a quiz.
	 *
	 * @return string The API endpoint.
	 */
	private function get_tutor_chat_api_endpoint() {
		return License_Manager::get_api_url() . '/senseilms-ai/v1/tutor-chat';
	}

	/**
	 * Create a response with the given status and data.
	 *
	 * @param string $status The status of the response.
	 * @param mixed  $data   The data of the response.
	 *
	 * @return array The response.
	 */
	private function create_response( $status, $data = null ) {
		return [
			'status' => $status,
			'data'   => $data,
		];
	}
}
