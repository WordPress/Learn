<?php
/**
 * @package Polylang
 */

namespace WP_Syntex\Polylang_Pro\Options\Business;

use WP_Error;
use WP_Syntex\Polylang\Options\Options;
use WP_Syntex\Polylang\Options\Abstract_Option;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Factory;
use WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Deepl as Service;


defined( 'ABSPATH' ) || exit;

/**
 * Class defining machine translation services array option.
 *
 * @since 3.7
 *
 * @phpstan-import-type DeeplOptions from \WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Services\Deepl
 */
class Machine_Translation_Services extends Abstract_Option {
	/**
	 * Returns option key.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return 'machine_translation_services'
	 */
	public static function key(): string {
		return 'machine_translation_services';
	}

	/**
	 * Sanitizes option's value.
	 * Can populate the `$errors` property with blocking and non-blocking errors: in case of non-blocking errors,
	 * the value is sanitized and can be stored.
	 *
	 * @since 3.8
	 *
	 * @param array   $value   Value to sanitize.
	 * @param Options $options All options.
	 * @return array|WP_Error The sanitized value. An instance of `WP_Error` in case of blocking error.
	 *
	 * @phpstan-return array{deepl: DeeplOptions}|WP_Error
	 */
	protected function sanitize( $value, Options $options ) {
		$value = parent::sanitize( $value, $options );

		if ( is_wp_error( $value ) ) {
			return $value;
		}

		/** @var array{deepl: DeeplOptions} $value */
		if ( empty( $value['deepl']['api_key'] ) ) {
			// Reset the glossary ID if the API key is empty.
			$value['deepl']['glossary_id'] = '';
		}

		return $value;
	}

	/**
	 * Adds information to the site health info array.
	 *
	 * @since 3.8
	 *
	 * @param Options $options An instance of the Options class providing additional configuration.
	 *
	 * @return array The updated site health information.
	 */
	public function get_site_health_info( Options $options ): array { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		/**
		 * @var array{deepl: DeeplOptions} $value
		 */
		$value = $this->get();

		if ( empty( $value['deepl']['api_key'] ) ) {
			return $this->format_single_value_for_site_health_info( __( 'DeepL is activated but no API key is set.', 'polylang-pro' ) );
		}
		$service = new Service(
			array(
				'api_key'   => $value['deepl']['api_key'],
				'formality' => $value['deepl']['formality'] ?? null,
			),
			PLL()->model
		);

		$result = $service->get_client()->is_api_key_valid();

		if ( $result->has_errors() ) {
			return $this->format_single_value_for_site_health_info( $result->get_error_message() );
		}

		return $this->format_single_value_for_site_health_info( __( 'The API key is valid', 'polylang-pro' ) );
	}

	/**
	 * Returns the default value.
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	protected function get_default() {
		$services = array();

		foreach ( Factory::get_classnames() as $service ) {
			$services[ $service::get_slug() ] = array();
		}

		return $services;
	}

	/**
	 * Returns the JSON schema part specific to this option.
	 *
	 * @since 3.7
	 *
	 * @return array Partial schema.
	 *
	 * @phpstan-return array{
	 *     type: 'object',
	 *     properties: array<
	 *         non-falsy-string,
	 *         array{
	 *             type: 'object',
	 *             properties: array,
	 *             additionalProperties: false
	 *         }
	 *     >,
	 *     additionalProperties: false
	 * }
	 */
	protected function get_data_structure(): array {
		$structure = array(
			'type'                 => 'object', // Correspond to associative array in PHP, @see{https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#primitive-types}.
			'properties'           => array(),
			'additionalProperties' => false,
		);

		foreach ( Factory::get_classnames() as $service ) {
			$structure['properties'][ $service::get_slug() ] = array(
				'type'                 => 'object',
				'properties'           => $service::get_option_schema(),
				'additionalProperties' => false,
			);
		}

		return $structure;
	}

	/**
	 * Returns the description used in the JSON schema.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_description(): string {
		return __( 'Settings for machine translation services: DeepL\'s API key, formality, and glossary for now.', 'polylang-pro' );
	}
}
