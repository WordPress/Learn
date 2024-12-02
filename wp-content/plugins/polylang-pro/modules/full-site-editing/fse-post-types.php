<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that adds the template (part) post type to the list of translatable ones.
 *
 * @since 3.2
 */
class PLL_FSE_Post_Types implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_post_types';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_filter( 'pll_get_post_types', array( $this, 'add_post_types' ), 10, 2 );
		add_filter( 'pll_rest_api_post_types', array( $this, 'remove_post_types_from_post_rest_api' ) );
		return $this;
	}

	/**
	 * Adds the template part post type to the list of translated post types.
	 *
	 * @since 3.2
	 *
	 * @param string[] $post_types  List of post type names.
	 * @param bool     $is_settings True when displaying the list of custom post types in Polylang settings.
	 * @return string[]
	 */
	public function add_post_types( $post_types = array(), $is_settings = false ) {
		if ( $is_settings || ! is_array( $post_types ) ) {
			return $post_types;
		}

		return array_merge( $post_types, PLL_FSE_Tools::get_template_post_types() );
	}

	/**
	 * Removes the translated template post type from the post types handled in `PLL_REST_Post`.
	 *
	 * @since 3.2
	 *
	 * @param array[] $post_types An array of arrays with post types as keys and options as values.
	 * @return array[] The post types without the translated template post type.
	 */
	public function remove_post_types_from_post_rest_api( $post_types ) {
		if ( ! is_array( $post_types ) ) {
			return $post_types;
		}

		return array_diff_key( $post_types, PLL_FSE_Tools::get_template_post_types() );
	}
}
