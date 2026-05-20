<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

use PLL_License;

defined( 'ABSPATH' ) || exit;

/**
 * Trait that holds common code between `Settings` and `Wizard_Licenses_Step`.
 *
 * @since 1.0
 */
trait Common_Trait {
	/**
	 * Stores an array of objects allowing to manage a license.
	 * In case of one plugin is using this new system, and another is using the old one, we must allow both classes.
	 *
	 * @var (License|PLL_License)[]|null
	 */
	private $items;

	/**
	 * Returns a list of license objects.
	 *
	 * @since 1.0
	 *
	 * @return (License|PLL_License)[] Array of instances of `WP_Syntex\Polylang_Pro\Updater\License` or `PLL_License`,
	 *                                 keyed by string IDs.
	 */
	private function get_licenses(): array {
		if ( is_array( $this->items ) ) {
			return $this->items;
		}

		/**
		 * Filters the list of Polylang licenses.
		 *
		 * @since 1.0
		 *
		 * @param (License|PLL_License)[] $licenses Array of instances of `WP_Syntex\Polylang_Pro\Updater\License`
		 *                                          or `PLL_License`, keyed by string IDs.
		 */
		$this->items = (array) apply_filters( 'pll_settings_licenses', array() );
		return $this->items;
	}

	/**
	 * Launches hooks.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_pllu_deactivate_license', array( $this, 'deactivate_license' ) );
	}

	/**
	 * Enqueues styles and scripts.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/*
		 * Use `__DIR__` in `plugins_url()` to get the path to a "file" located at the root of this package. Keep that
		 * in mind if this file is moved.
		 */
		$style_url = plugins_url( "/css/build/license{$suffix}.css", __DIR__ );
		wp_enqueue_style( 'pll_license', $style_url, array(), Updater::get_version() );

		$script_url = plugins_url( "/js/build/license{$suffix}.js", __DIR__ );
		wp_enqueue_script( 'pll_license', $script_url, array( 'wp-api-fetch' ), Updater::get_version(), true );
	}

	/**
	 * Ajax method to deactivate a license.
	 * Hooked to `wp_ajax_pllu_deactivate_license`.
	 *
	 * Requires the class constant `DEACTIVATE_LICENSE_NONCE_ACTION`.
	 *
	 * @since 1.0
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function deactivate_license(): void {
		check_ajax_referer( self::DEACTIVATE_LICENSE_NONCE_ACTION, '_pll_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		if ( ! isset( $_POST['id'] ) ) {
			wp_die( 0 );
		}

		$id       = substr( sanitize_text_field( wp_unslash( $_POST['id'] ) ), 11 );
		$licenses = $this->get_licenses();

		if ( ! isset( $licenses[ $id ] ) ) {
			wp_die( 0 );
		}

		$license = $licenses[ $id ]->deactivate_license();

		wp_send_json_success(
			array(
				'id'   => $id,
				'html' => $license->get_form_field(),
			)
		);
	}
}
