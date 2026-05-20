<?php
/**
 * @package Polylang-Pro
 */

/**
 * Trait to share logic to manage strings metabox forms.
 *
 * @since 3.7
 */
trait PLL_Strings_Form_Trait {
	/**
	 * Sanitizes and validates a list of language slugs, and returns language objects.
	 *
	 * @since 3.6
	 * @since 3.7 Moved from PLL_Export_Strings_Action.
	 *
	 * @param array $languages Language slugs.
	 * @return PLL_Language[]|WP_Error An array of `PLL_Language` objects. A `WP_Error` object on failure.
	 *
	 * @phpstan-return non-empty-array<PLL_Language>|WP_Error
	 */
	private function get_sanitized_languages( array $languages ) {
		if ( empty( $languages ) ) {
			return new WP_Error( 'pll_export_no_target_languages', __( 'Error: Please select a target language.', 'polylang-pro' ) );
		}

		$languages = array_filter( $languages, 'is_string' );
		$languages = array_map( 'sanitize_key', $languages );
		$languages = array_map( array( $this->model, 'get_language' ), $languages );
		$languages = array_filter( $languages );

		if ( empty( $languages ) ) {
			return new WP_Error( 'invalid-target-languages', __( 'Error: invalid target languages.', 'polylang-pro' ) );
		}

		return $languages;
	}

	/**
	 * Registers settings errors to be displayed to the user.
	 *
	 * @since 3.6
	 * @since 3.7 Moved from PLL_Export_Strings_Action.
	 *
	 * @param WP_Error $error An error object.
	 * @return void
	 *
	 * @phpstan-return never
	 */
	private function display_errors( WP_Error $error ): void {
		pll_add_notice( $error );

		PLL_Settings::redirect();
	}
}
