<?php
/**
 * File containing the class \Sensei_Pro_Setup\WPCOM_Marketplace_License_Manager.
 *
 * @package sensei-pro
 * @since   1.11.0
 */

namespace Sensei_Pro_Setup;

use SenseiLMS_Licensing\License_Manager;

/**
 * The WPCOM_Marketplace_License_Manager class.
 */
class WPCOM_Marketplace_License_Manager {
	/**
	 * Singleton instance.
	 *
	 * @var WPCOM_Marketplace_License_Manager
	 */
	private static $instance;

	/**
	 * List of products registered on WPCOM.
	 */
	private const SENSEI_WPCOM_PRODUCTS = [
		'sensei-pro',
		'sensei-interactive-blocks',
	];

	/**
	 * Fetches an instance of the class.
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
	 * Initialize the class and adds all the filters required to handle webhook responses triggered by WPCOM.
	 *
	 * @return void
	 */
	public function init() {
		foreach ( self::SENSEI_WPCOM_PRODUCTS as $sensei_wpcom_product ) {
			add_filter( 'wpcom_marketplace_webhook_response_' . $sensei_wpcom_product, [ $this, 'handle_webhook_response' ], 10, 3 );
		}

		add_filter( 'sensei_wpcom_product_slugs', [ $this, 'add_sensei_wpcom_product_slugs' ] );
	}

	/**
	 * Configure license configuration for Sensei when purchased from WPCOM marketplace.
	 *
	 * @param bool|\WP_Error $result The result of the licensing configuration.
	 * @param array          $payload The payload received from SenseiLMS.com back-end API.
	 * @param string         $event_type The event type that triggered this filter.
	 * @internal
	 *
	 * @return bool|\WP_Error
	 */
	public function handle_webhook_response( $result, $payload, $event_type ) {
		if ( 'provision_license' !== $event_type ) {
			return $result;
		}
		$sensei_product_slugs = explode( ',', $payload['sensei_product_slugs'] );
		foreach ( $sensei_product_slugs as $sensei_product_slug ) {
			$activation_result = License_Manager::activate_license( $sensei_product_slug, $payload['license_code'] );
			if ( false === $activation_result || false === $activation_result->success ) {
				return new \WP_Error( 'error', 'An error has occurred while installing license for ' . $sensei_product_slug );
			}
		}
		return $result;
	}

	/**
	 * Adds the known slugs of WPCOM products to the list of products verified by Sensei_Utils::has_wpcom_subscription.
	 *
	 * @param array $sensei_wpcom_product_slugs Array of product slugs to check if it has an active WPCOM subscription.
	 *
	 * @return array New array of product slugs.
	 */
	public function add_sensei_wpcom_product_slugs( $sensei_wpcom_product_slugs ) {
		// We add woothemes-sensei here because it is a valid WPCOM subscription which might be redirected to WPCOM
		// support, however, it is NOT something that would be enabled on senseilms.com, so we don't want to listen
		// to the webhook for it.
		return array_merge( $sensei_wpcom_product_slugs, self::SENSEI_WPCOM_PRODUCTS, [ 'woothemes-sensei' ] );
	}
}
