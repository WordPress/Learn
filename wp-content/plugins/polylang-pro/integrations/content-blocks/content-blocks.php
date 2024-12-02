<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to manage the integration with Content Blocks (Custom Post Widget).
 * Version tested: 3.0.4.
 *
 * @since 2.6
 */
class PLL_Content_Blocks {

	/**
	 * Initializes filters and actions.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'pll_get_post_types', array( $this, 'get_post_types' ), 10, 2 );
	}

	/**
	 * Add the Content Blocks post type to the list of translatable post types.
	 *
	 * @since 2.6
	 *
	 * @param string[] $post_types  List of post types.
	 * @param bool     $is_settings True when displaying the list of custom post types in Polylang settings.
	 * @return string[]
	 */
	public function get_post_types( $post_types, $is_settings ) {
		if ( ! $is_settings ) {
			$post_types['content_block'] = 'content_block';
		}
		return $post_types;
	}
}
