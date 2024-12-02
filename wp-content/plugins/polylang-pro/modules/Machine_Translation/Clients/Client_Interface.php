<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation\Clients;

use Translations;
use PLL_Language;
use WP_Error;

/**
 * Interface to use to define a machine translation client.
 */
interface Client_Interface {
	/**
	 * Performs a request to machine translation service.
	 *
	 * @since 3.6
	 *
	 * @param Translations      $translations    Translations object.
	 * @param PLL_Language      $target_language Target language.
	 * @param PLL_Language|null $source_language Source language, leave empty for automatic detection.
	 * @return Translations|WP_Error
	 */
	public function translate( Translations $translations, PLL_Language $target_language, $source_language = null );

	/**
	 * Tells whether API key is valid.
	 *
	 * @since 3.6
	 *
	 * @return WP_Error An empty WP_Error if valid, a filled WP_Error otherwise.
	 */
	public function is_api_key_valid(): WP_Error;

	/**
	 * Returns current machine translation usage.
	 *
	 * @since 3.6
	 *
	 * @return array|WP_Error
	 */
	public function get_usage();
}
