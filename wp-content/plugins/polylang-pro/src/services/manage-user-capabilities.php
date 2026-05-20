<?php
/**
 * @package Polylang-Pro
 */

/**
 * A Service to manage current user capabilities.
 *
 * @since 3.3
 */
class PLL_Manage_User_Capabilities {

	/**
	 * Removes the 'unfiltered_html' capability from the current user.
	 *
	 * @since 3.3
	 *
	 * @param WP_Post $source_post The source post about to be translated.
	 * @return void
	 */
	public function forbid_unfiltered_html( $source_post ) {
		/**
		 * Filters the 'unfiltered_html' capability. Disabled by default during XLIFF import.
		 *
		 * @since 3.3
		 *
		 * @param bool    $is_enabled  Whether the 'unfiltered_html' capability is enabled. Default to false.
		 * @param WP_User $user        Current user object.
		 * @param WP_Post $source_post Source post currently translated.
		 */
		$force_unfiltered_html = apply_filters( 'pll_allow_import_unfiltered_html', false, wp_get_current_user(), $source_post );

		if ( ! $force_unfiltered_html ) {
			add_filter( 'map_meta_cap', array( $this, 'remove_unfiltered_html_cap' ), 10, 2 );
			kses_init();
		}
	}

	/**
	 * Sets 'unfiltered_html' capability to default for users.
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function allow_unfiltered_html() {
		remove_filter( 'map_meta_cap', array( $this, 'remove_unfiltered_html_cap' ), 10 );
		kses_init();
	}

	/**
	 * Disallows 'unfiltered_html' capability.
	 *
	 * @since 3.3
	 *
	 * @param string[] $caps Primitive capabilities required of the user.
	 * @param string   $cap  Capability being checked.
	 * @return string[] Filtered primitive capabilities.
	 */
	public function remove_unfiltered_html_cap( $caps, $cap ) {
		if ( 'unfiltered_html' === $cap ) {
			$caps[] = 'do_not_allow';
		}
		return $caps;
	}
}
