<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities;

use PLL_Language;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang\Model\Languages_Proxy_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class allowing to filter the list of languages, depending on the current user's capabilities.
 *
 * @since 3.8
 */
class Languages_Proxy implements Languages_Proxy_Interface {
	/**
	 * Returns the proxy's key.
	 *
	 * @since 3.8
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	public function key(): string {
		return 'translator';
	}

	/**
	 * Returns the list of available languages after passing it through this proxy.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Language[] $languages List of languages to filter.
	 * @return PLL_Language[]
	 */
	public function filter( array $languages ): array {
		$user = Capabilities::get_user();

		if ( ! $user->is_translator() ) {
			return $languages;
		}

		return array_filter( $languages, array( $user, 'can_translate' ) );
	}
}
