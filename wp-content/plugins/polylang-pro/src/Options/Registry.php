<?php
/**
 * @package Polylang
 */

namespace WP_Syntex\Polylang_Pro\Options;

use WP_Syntex\Polylang\Options\Registry as PLL_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Polylang Pro's options registry.
 *
 * @since 3.7
 */
class Registry extends PLL_Registry {
	protected const OPTIONS = array(
		Business\Media::class,
		Business\Machine_Translation_Enabled::class,
		Business\Machine_Translation_Services::class,
	);
}
