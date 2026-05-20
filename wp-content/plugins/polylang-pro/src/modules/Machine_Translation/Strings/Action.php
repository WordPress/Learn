<?php
/**
 * @package Polyang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Strings;

use WP_Error;
use PLL_Model;
use PLL_Language;
use PLL_Admin_Base;
use PLL_Admin_Strings;
use PLL_Export_Container;
use PLL_Strings_Form_Trait;
use PLL_Export_Strings_Action;
use PLL_Export_Data_From_Strings;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Data;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Processor;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Service_Interface;

/**
 * Class to manage the whole process of the machine translations of strings.
 *
 * @since 3.7
 */
class Action {
	use PLL_Strings_Form_Trait;

	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * @var Processor
	 */
	private $processor;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Admin_Base    $polylang Instance of Polylang main object for settings page.
	 * @param Service_Interface $service  Instance of the machine translation service.
	 */
	public function __construct( PLL_Admin_Base $polylang, Service_Interface $service ) {
		$this->model     = $polylang->model;
		$this->processor = new Processor( $polylang, $service->get_client() );
	}

	/**
	 * Translates the strings of a given group.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Language $target_language The language to translate the strings to.
	 * @param string       $group           The group of strings to translate.
	 * @return WP_Error Error object, empty if no error.
	 */
	public function translate( PLL_Language $target_language, string $group ): WP_Error {
		$sources = PLL_Admin_Strings::get_strings();
		if ( '' !== $group ) {
			$sources = array_filter(
				$sources,
				function ( $source ) use ( $group ) {
					return $group === $source['context'];
				}
			);
		}

		$exporter  = new PLL_Export_Data_From_Strings( $this->model );
		$container = new PLL_Export_Container( Data::class );
		$exporter->send_to_export(
			$container,
			$sources,
			$target_language,
			true
		);

		$result = $this->processor->translate( $container );

		if ( $result->has_errors() ) {
			return $result;
		}

		return $this->processor->save( $container );
	}

	/**
	 * Validates the form and translates the strings.
	 *
	 * @since 3.7
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function validate_form() {
		check_admin_referer( PLL_Export_Strings_Action::ACTION_NAME, PLL_Export_Strings_Action::NONCE_NAME );

		if ( ! isset( $_POST['target-lang'], $_POST['group'] ) ) {
			$error = new WP_Error( 'pll_incomplete_form', __( 'Sorry, some data is missing from the form submission.', 'polylang-pro' ) );
			$this->display_errors( $error );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$target_languages = is_array( $_POST['target-lang'] ) ? wp_unslash( $_POST['target-lang'] ) : array();
		$target_languages = $this->get_sanitized_languages( $target_languages );

		if ( is_wp_error( $target_languages ) ) {
			$this->display_errors( $target_languages );
		}

		$user = Capabilities::get_user();
		foreach ( $target_languages as $target_language ) {
			$user->can_translate_or_die( $target_language );
		}

		$group  = is_string( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '';
		$group  = '-1' === $group ? '' : $group;
		$errors = new WP_Error();

		foreach ( $target_languages as $target_language ) {
			$errors->merge_from( $this->translate( $target_language, $group ) );
		}

		$this->display_errors( $errors );
	}
}
