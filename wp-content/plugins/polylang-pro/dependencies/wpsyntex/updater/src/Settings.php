<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

use PLL_License;
use PLL_Settings;
use PLL_Settings_Module;
use WP_Ajax_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class for licenses.
 *
 * @since 1.0
 */
class Settings extends PLL_Settings_Module {
	use Common_Trait;

	/**
	 * Name of the ajax action to get the licenses data.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const GET_LICENSES_DATA_ACTION = 'pllu_get_licenses_data';

	/**
	 * Name of the action to create the nonce required to deactivate a license.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const DEACTIVATE_LICENSE_NONCE_ACTION = 'pll_options';

	/**
	 * Stores the display order priority.
	 *
	 * @var int
	 */
	public $priority = 100;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param PLL_Settings $polylang Polylang object.
	 */
	public function __construct( PLL_Settings &$polylang ) {
		parent::__construct(
			$polylang,
			array(
				'module'      => 'licenses',
				'title'       => __( 'License keys', 'polylang-pro' ),
				'description' => __( 'Manage licenses for Polylang Pro and add-ons.', 'polylang-pro' ),
			)
		);

		$this->buttons['cancel'] = sprintf( '<button type="button" class="button button-secondary cancel">%s</button>', __( 'Close', 'polylang-pro' ) );

		$this->hooks();

		/*
		 * Useless because replaced by the following hook, so let's remove it so it doesn't clutter
		 * the global `$wp_filter`.
		 */
		remove_action( 'wp_ajax_pll_save_options', array( $this, 'save_options' ) );

		/*
		 * Before `PLL_Settings_Module::save_options()`, to ensure that our response to
		 * ajax action is executed and not the Polylang legacy licenses system.
		 */
		add_action( 'wp_ajax_pll_save_options', array( $this, 'save_options' ), 5 );

		add_action( 'wp_ajax_' . self::GET_LICENSES_DATA_ACTION, array( $this, 'display_licenses' ) );
	}

	/**
	 * Tells if the module is active.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return ! empty( $this->get_licenses() );
	}

	/**
	 * Displays the settings form.
	 *
	 * @since 1.0
	 */
	protected function form(): void {
		if ( ! $this->is_active() ) {
			return;
		}

		$atts = array(
			'action' => self::GET_LICENSES_DATA_ACTION,
			'nonce'  => wp_create_nonce( self::GET_LICENSES_DATA_ACTION ),
		);

		include __DIR__ . '/views/view-settings-licenses.php';
	}

	/**
	 * Ajax method to save the license keys and activate the licenses at the same time.
	 * Overrides the parent's method.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function save_options(): void {
		check_ajax_referer( 'pll_options', '_pll_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		if ( ! isset( $_POST['module'] ) || $this->module !== $_POST['module'] || empty( $_POST['licenses'] ) ) {
			return;
		}

		$response = new WP_Ajax_Response();
		$what     = 'success';

		foreach ( $this->get_licenses() as $item ) {
			if ( ! isset( $_POST['licenses'][ $item->id ] ) ) {
				continue;
			}

			$updated_item = $item->activate_license( sanitize_key( $_POST['licenses'][ $item->id ] ) );
			$response->Add(
				array(
					'what'         => 'pllu-license-update',
					'data'         => $item->id,
					'supplemental' => array( 'html' => $updated_item->get_form_field() ),
				)
			);

			if ( empty( $updated_item->license_data->success ) ) {
				$what = 'error';
			}
		}

		// Updated message.
		pll_add_notice( new WP_Error( 'settings_updated', __( 'Settings saved.', 'polylang-pro' ), 'success' ) );

		ob_start();
		settings_errors( 'polylang' );
		$response->Add(
			array(
				'what' => $what,
				'data' => (string) ob_get_clean(),
			)
		);
		$response->send();
	}

	/**
	 * Ajax method to display licenses with their data (deactivation, expiration date).
	 * Hooked to `wp_ajax_pllu_get_licenses_data`.
	 *
	 * @since 1.0
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function display_licenses(): void {
		check_ajax_referer( self::GET_LICENSES_DATA_ACTION, '_pll_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$rows = array();
		foreach ( $this->get_licenses() as $license ) {
			$license->check_license(); // Get the license information from the server to ensure it's up to date.
			$rows[] = $license->get_form_field();
		}

		wp_send_json_success(
			array(
				'row' => $rows,
			)
		);
	}
}
