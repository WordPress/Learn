<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Translation_Walker_Interface
 *
 * Define interface for classes that iterate over content.
 *
 * @since 3.3
 */
interface PLL_Translation_Walker_Interface {
	/**
	 * Iterates over each translatable part of a content and applies a callback function on each part.
	 *
	 * @since 3.3
	 *
	 * @param callable $callback A callback function.
	 * @return string The content (potentially) modified by the callback.
	 */
	public function walk( $callback );
}
