<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF;

use PLL_Abstract_Deactivate;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Labels\Field_Groups;

defined( 'ABSPATH' ) || exit;

/**
 * ACF integration deactivation class.
 *
 * @since 3.8
 */
class Deactivate extends PLL_Abstract_Deactivate {
	/**
	 * Plugin deactivation.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	protected static function process(): void {
		Field_Groups::clear_cache();
	}
}
