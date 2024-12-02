<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

defined( 'ABSPATH' ) || exit;

use PLL_Base;
use PLL_Toggle_User_Meta;
use PLL_Metabox_User_Button;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Service_Interface;

/**
 * Adds a button in the languages metabox (classic editor), allowing to create a new translation by using a machine
 * translation service.
 *
 * @since 3.6
 */
class Button extends PLL_Metabox_User_Button {
	/**
	 * Instance of the machine translation service.
	 *
	 * @var Service_Interface
	 */
	protected $service;

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

		$args = array(
			'position'   => 'before_post_translations',
			'activate'   => sprintf(
				/* translators: %s is the name of the machine translation service. */
				__( 'Activate %s machine translation', 'polylang-pro' ),
				$this->service->get_name()
			),
			'deactivate' => sprintf(
				/* translators: %s is the name of the machine translation service. */
				__( 'Deactivate %s machine translation', 'polylang-pro' ),
				$this->service->get_name()
			),
			'icon'       => $this->service->get_icon(),
			'priority'   => 20,
		);

		parent::__construct( 'pll-machine-translation', $args );

		add_action( 'admin_notices', array( $this, 'display_errors' ) );
	}

	/**
	 * Prints translation errors into the page.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function display_errors() {
		settings_errors( 'polylang' );
	}
}
