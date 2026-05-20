<?php
/**
 * @package Polylang
 */

namespace WP_Syntex\Polylang_Pro\Options\Business;

use WP_Syntex\Polylang\Options\Primitive\Abstract_Boolean;

defined( 'ABSPATH' ) || exit;

/**
 * Class defining machine translation boolean option.
 *
 * @since 3.7
 */
class Machine_Translation_Enabled extends Abstract_Boolean {
	/**
	 * Returns option key.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return 'machine_translation_enabled'
	 */
	public static function key(): string {
		return 'machine_translation_enabled';
	}

	/**
	 * Returns the description used in the JSON schema.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_description(): string {
		return __( 'Enable machine translation.', 'polylang-pro' );
	}
}
