<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Active_Languages;

use PLL_Language;
use WP_Syntex\Polylang\Model\Languages_Proxy_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class to filter the list of languages to only include active languages, depending on the current user's capabilities.
 *
 * @since 3.8
 */
class Languages_Proxy implements Languages_Proxy_Interface {
	public const CAPABILITY = 'edit_posts';

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
		return 'hide_inactive';
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
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			return $languages;
		}

		if ( current_user_can( self::CAPABILITY ) ) {
			return $languages;
		}

		foreach ( $languages as $k => $lang ) {
			if ( empty( $lang->active ) ) {
				unset( $languages[ $k ] );
			}
		}

		return $languages;
	}
}
