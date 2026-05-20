<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

use PLL_Wizard;

defined( 'ABSPATH' ) || exit;

/**
 * A class to handle the wizard license step.
 * Must be instantiated on `pll_init` or later (after `PLL_Wizard` is instantiated).
 *
 * @since 1.0
 *
 * @phpstan-type Steps array<
 *     string,
 *     array{
 *         name: string,
 *         view: callable,
 *         handler: callable,
 *         scripts: non-falsy-string[],
 *         styles: non-falsy-string[]
 *     }
 * >
 */
class Wizard_Licenses_Step {
	use Common_Trait;

	/**
	 * Name of the action to create the nonce required to deactivate a license.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const DEACTIVATE_LICENSE_NONCE_ACTION = 'pll-wizard';

	/**
	 * Instance of the wizard.
	 *
	 * @var PLL_Wizard
	 */
	private $wizard;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param PLL_Wizard $wizard Instance of the wizard.
	 */
	public function __construct( PLL_Wizard $wizard ) {
		$this->wizard = $wizard;

		add_filter( 'pll_wizard_steps', array( $this, 'add' ), 101 ); // After `PLL_Wizard::add_step_licenses()`, so this can override it.
	}

	/**
	 * Adds licenses step to the wizard.
	 *
	 * @since 1.0
	 *
	 * @param array $steps List of steps.
	 * @return array List of steps updated.
	 *
	 * @phpstan-param Steps $steps
	 * @phpstan-return Steps
	 */
	public function add( $steps ) {
		if ( isset( $steps['licenses']['view'] ) && is_array( $steps['licenses']['view'] ) && isset( $steps['licenses']['view'][0] ) ) {
			$first_step = $steps['licenses']['view'][0];
			if ( $first_step instanceof self ) {
				// No need to execute this several times.
				return $steps;
			}
			if ( $first_step instanceof PLL_Wizard ) {
				// Remove the old system, so we don't send 2 AJAX requests for the same action (deactivate license).
				remove_action( 'wp_ajax_pll_deactivate_license', array( $first_step, 'deactivate_license' ) );
				wp_dequeue_script( 'pll_settings' );
			}
		}

		$this->hooks();

		if ( ! empty( $this->get_licenses() ) ) {
			$steps['licenses'] = array(
				'name'    => esc_html__( 'Licenses', 'polylang-pro' ),
				'view'    => array( $this, 'display' ),
				'handler' => array( $this, 'save' ),
				'scripts' => array( 'pll_license' ),
				'styles'  => array( 'pll_license' ),
			);
		}

		return $steps;
	}

	/**
	 * Displays the languages step form.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display(): void {
		$atts = array(
			'license_rows' => array(),
			'is_error'     => isset( $_GET['activate_error'] ) && 'i18n_license_key_error' === sanitize_key( $_GET['activate_error'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
		foreach ( $this->get_licenses() as $license ) {
			$atts['license_rows'][] = $license->get_form_field();
		}

		include __DIR__ . '/views/view-wizard-step-licenses.php';
	}

	/**
	 * Executes the languages step.
	 *
	 * @since 1.0
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function save(): void {
		// User capabilities verified in `PLL_Wizard::setup_wizard_page()`.
		check_admin_referer( 'pll-wizard', '_pll_nonce' );

		$redirect = $this->wizard->get_next_step_link();

		foreach ( $this->get_licenses() as $license ) {
			if ( ! isset( $_POST['licenses'][ $license->id ] ) ) {
				continue;
			}

			$updated_license = $license->activate_license( sanitize_key( $_POST['licenses'][ $license->id ] ) );

			if ( empty( $updated_license->license_data ) || false !== $updated_license->license_data->success ) {
				// Success.
				continue;
			}

			// Stay on this step with an error.
			$redirect = add_query_arg(
				array(
					'step'           => $this->get_current_step(),
					'activate_error' => 'i18n_license_key_error',
				)
			);
		}

		wp_safe_redirect( sanitize_url( $redirect ) );
		exit;
	}

	/**
	 * Returns the current step.
	 *
	 * @since 1.0
	 * @see PLL_Wizard::setup_wizard_page()
	 *
	 * @return string
	 */
	private function get_current_step(): string {
		/** This filter is documented in `PLL_Wizard::setup_wizard_page()`. */
		$steps = apply_filters( 'pll_wizard_steps', array() );
		$step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification

		// @phpstan-var non-empty-array<string, array> $steps
		// @phpstan-var string|false $step
		return $step && array_key_exists( $step, $steps ) ? $step : key( $steps );
	}
}
