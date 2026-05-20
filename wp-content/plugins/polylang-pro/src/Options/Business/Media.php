<?php
/**
 * @package Polylang
 */

namespace WP_Syntex\Polylang_Pro\Options\Business;

use WP_Syntex\Polylang\Options\Abstract_Option;
use WP_Syntex\Polylang\Options\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class defining media array option.
 *
 * @since 3.7
 */
class Media extends Abstract_Option {
	/**
	 * Returns option key.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return 'media'
	 */
	public static function key(): string {
		return 'media';
	}

	/**
	 * Returns the default value.
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	protected function get_default() {
		return array( 'duplicate' => false );
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
		$key = self::key();
		$fields = array();

		/** @var array<string, mixed> $value */
		$value = $this->get();
		foreach ( $value as $sub_key => $sub_value ) {
			if ( $sub_value ) {
				$fields[ "$key-$sub_key" ] = __( 'Enabled', 'polylang-pro' );
			}
		}

		return $this->format_single_value_for_site_health_info( $this->format_array_for_site_health_info( $fields ) );
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
	 *     properties: array{
	 *         duplicate: array{
	 *             type: 'boolean',
	 *             required: true
	 *         }
	 *     },
	 *     additionalProperties: false
	 * }
	 */
	protected function get_data_structure(): array {
		return array(
			'type'                 => 'object', // Correspond to associative array in PHP, @see{https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#primitive-types}.
			'properties'           => array(
				'duplicate' => array(
					'type'     => 'boolean',
					'required' => true,
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Returns the description used in the JSON schema.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_description(): string {
		return __( 'Automatically duplicate media in all languages when uploading a new file.', 'polylang-pro' );
	}
}
