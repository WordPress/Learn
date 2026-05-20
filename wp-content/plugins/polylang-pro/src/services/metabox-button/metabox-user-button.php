<?php
/**
 * @package Polylang Pro
 */

/**
 * Abstract class for non-rest user meta related button.
 */
abstract class PLL_Metabox_User_Button extends PLL_Metabox_Button {
	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Tells whether the button is active or not.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->user_meta->is_active();
	}

	/**
	 * Saves the button state.
	 *
	 * @since 3.6
	 *
	 * @param string $post_type Current post type.
	 * @param bool   $active    New requested button state.
	 * @return bool Whether the new button state is accepted or not.
	 */
	protected function toggle_option( $post_type, $active ) {
		return $this->user_meta->toggle_option( $post_type, $active );
	}
}
