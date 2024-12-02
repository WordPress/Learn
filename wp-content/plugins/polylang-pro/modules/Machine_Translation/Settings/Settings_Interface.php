<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Settings;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Machine translation settings.
 *
 * @since 3.6
 */
interface Settings_Interface {
	/**
	 * Sanitizes and validates the options for this service.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service.
	 * @return array Validated options.
	 */
	public function sanitize_options( array $options ): array;

	/**
	 * Tells if the given service options contain a non-empty authentication key.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service.
	 * @return bool
	 */
	public function has_api_key( array $options ): bool;

	/**
	 * Tells if the authentication key from the given service options is valid by contacting the service.
	 *
	 * @since 3.6
	 *
	 * @param array $options Options for this service (must be sanitized beforehand).
	 * @return WP_Error {
	 *    An empty `WP_Error` if the authentication succeeded.
	 *    In the other cases, the `WP_Error` data will contain an array as follow:
	 *
	 *    @type string $type     `'error'` if the API key is invalid, or `'warning'` if there was an error while
	 *                           contacting the service.
	 *    @type string $field_id CSS ID of the field in fault.
	 * }
	 */
	public function is_api_key_valid( array $options ): WP_Error;

	/**
	 * Prints error notices.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function print_notices();

	/**
	 * Prints settings fields.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function print_settings_fields();
}
