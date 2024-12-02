<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

defined( 'ABSPATH' ) || exit;

use PLL_Base;
use PLL_Toggle_User_Meta;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Service_Interface;

/**
 * Adds a button in the languages metabox (block editor), allowing to create a new translation by using a machine
 * translation service.
 * Exposes the pll_machine_translation user meta in the REST API.
 *
 * @since 3.6
 */
class Button_REST {
	/**
	 * Instance of the machine translation service.
	 *
	 * @var Service_Interface
	 */
	protected $service;

	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Base          $polylang Polylang object. Unused but passed by `PLL_Admin_Loader` anyway...
	 * @param Service_Interface $service  Machine translation service.
	 */
	public function __construct( PLL_Base $polylang, Service_Interface $service ) {
		$this->service   = $service;
		$this->user_meta = new PLL_Toggle_User_Meta( sprintf( 'pll_machine_translation_%s', $this->service->get_slug() ) );

		register_rest_field(
			'user',
			$this->user_meta->get_meta_name(),
			array(
				'get_callback'    => array( $this->user_meta, 'get' ),
				'update_callback' => array( $this->user_meta, 'update' ),
			)
		);

		add_filter( 'pll_block_editor_plugin_settings', array( $this, 'get_service_settings' ) );
		add_filter( 'pll_block_editor_plugin_settings', array( $this, 'get_settings_errors' ) );
	}

	/**
	 * Adds service properties in UI settings.
	 *
	 * @since 3.6
	 *
	 * @param array $settings UI settings.
	 * @return array Updated UI settings.
	 */
	public function get_service_settings( $settings ) {
		$settings['machine_translation'] = array(
			'slug'     => $this->service::get_slug(),
			'name'     => $this->service->get_name(),
			'icon'     => $this->service->get_icon_properties(),
			'isActive' => $this->service->is_active(),
		);
		return $settings;
	}

	/**
	 * Adds machine translation errors in UI settings.
	 *
	 * @since 3.6
	 *
	 * @param array $settings UI settings.
	 * @return array Updated UI settings.
	 */
	public function get_settings_errors( $settings ) {
		$settings_errors = get_settings_errors( 'polylang' );

		if ( empty( $settings_errors ) ) {
			return $settings;
		}

		$settings['machine_translation']['errors'] = $settings_errors;

		return $settings;
	}
}
