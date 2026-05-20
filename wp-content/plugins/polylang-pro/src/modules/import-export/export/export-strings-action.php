<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang\Capabilities\Capabilities;

/**
 * A class that handles the export action of string translations.
 *
 * @since 3.6
 */
class PLL_Export_Strings_Action {
	use PLL_Strings_Form_Trait;

	/**
	 * Used to set the action's name in forms.
	 *
	 * @var string
	 */
	const ACTION_NAME = 'pll_translate';

	/**
	 * Used to create nonces for the action.
	 *
	 * @var string
	 */
	const NONCE_NAME = '_pll_translate_nonce';

	/**
	 * Used to set export action value.
	 *
	 * @var string
	 */
	const ACTION_VALUE = 'export-translations';

	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * A class to handle file download.
	 *
	 * @var PLL_Export_Download
	 */
	private $downloader;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Model           $model      Instance of `PLL_Model`.
	 * @param PLL_Export_Download $downloader Instance of `PLL_Export_Download`.
	 */
	public function __construct( PLL_Model $model, PLL_Export_Download $downloader ) {
		$this->model      = $model;
		$this->downloader = $downloader;
	}

	/**
	 * Action for string translations export.
	 * Make sure to verify the current user's capabilities first.
	 * Expects the following `$_POST` values:
	 *     - `target-lang` string[] Array of language slugs.
	 *     - `filetype`    string   A file extension, like `po`.
	 *     - `group`       string   Allows to export string translations from the given group only.
	 *                              Use `-1` or an empty string to export all strings.
	 *
	 * @since 3.6
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function export_action(): void {
		check_admin_referer( self::ACTION_NAME, self::NONCE_NAME );

		if ( ! isset( $_POST['target-lang'], $_POST['filetype'], $_POST['group'] ) ) {
			$error = new WP_Error( 'pll_incomplete_form', __( 'Sorry, some data is missing from the form submission.', 'polylang-pro' ) );
			$this->display_errors( $error );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$target_languages = is_array( $_POST['target-lang'] ) ? wp_unslash( $_POST['target-lang'] ) : array();
		$target_languages = $this->get_sanitized_languages( $target_languages );

		if ( is_wp_error( $target_languages ) ) {
			$this->display_errors( $target_languages );
		}

		$file_type = is_string( $_POST['filetype'] ) ? sanitize_key( $_POST['filetype'] ) : '';
		$group     = is_string( $_POST['group'] ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '';
		$group     = '-1' === $group ? '' : $group;

		$errors = $this->export( $target_languages, $file_type, $group );

		// At this point, the zip file creation failed.
		$this->display_errors( $errors );
	}

	/**
	 * Launches the string translations export.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language[] $target_languages Target language slugs.
	 * @param string         $file_type        File type.
	 * @param string         $group            Optional. Allows to export string translations from the given group only.
	 *                                         Use an empty string to export all strings. Default is an empty string.
	 * @return WP_Error
	 */
	public function export( array $target_languages, string $file_type, string $group = '' ): WP_Error {
		$file_format_factory = new PLL_File_Format_Factory();
		$filetype            = $file_format_factory->split_filetype( sanitize_key( $file_type ) );
		$file_format         = $file_format_factory->from_extension( $filetype['extension'] );

		if ( is_wp_error( $file_format ) ) {
			return $file_format;
		}

		$export_container = new PLL_Export_Container( $file_format->get_export_class( $filetype['version'] ) );
		$export           = new PLL_Export_Data_From_Strings( $this->model );
		$sources          = PLL_Admin_Strings::get_strings();
		if ( '' !== $group ) {
			$sources = array_filter(
				$sources,
				function ( $source ) use ( $group ) {
					return $group === $source['context'];
				}
			);
		}

		$user = Capabilities::get_user();

		foreach ( $target_languages as $target_language ) {
			if ( ! $user->can_translate( $target_language ) ) {
				wp_die(
					esc_html(
						sprintf(
							/* translators: %s is a language name. */
							__( 'Sorry, you are not allowed to export translations in %s.', 'polylang-pro' ),
							$target_language->name
						)
					),
					403
				);
			}

			$errors = $export->send_to_export( $export_container, $sources, $target_language );

			if ( $errors->has_errors() ) {
				return $errors;
			}
		}

		return $this->downloader->create( $export_container );
	}
}
