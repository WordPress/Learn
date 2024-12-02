<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Translation_Walker_Factory
 *
 * A factory to create a translation walker with a given content.
 *
 * @since 3.3
 */
class PLL_Translation_Walker_Factory {
	/**
	 * Generates the correct walker class for the content to be walked.
	 *
	 * @since 3.3
	 *
	 * @param string $content A content to iterate over.
	 * @return PLL_Translation_Walker_Interface
	 */
	public static function create_from( $content ) {
		if ( function_exists( 'has_blocks' ) && has_blocks( $content ) ) {
			$walker = new PLL_Translation_Walker_Blocks( $content );
		} else {
			$walker = new PLL_Translation_Walker_Classic( $content );
		}
		return $walker;
	}
}
