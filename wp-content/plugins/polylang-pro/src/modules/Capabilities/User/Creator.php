<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\User;

use WP_User;
use WP_Syntex\Polylang\Capabilities\User\NOOP;
use WP_Syntex\Polylang\Capabilities\User\User_Interface;
use WP_Syntex\Polylang\Capabilities\User\Creator_Interface;

/**
 * A class that returns the correct user instance based on the user capabilities.
 * Completely disables the user decoration.
 *
 * @since 3.8
 */
class Creator implements Creator_Interface {
	/**
	 * @var User_Interface
	 */
	private ?User_Interface $instance = null;

	/**
	 * Creates and returns the user.
	 *
	 * @since 3.8
	 *
	 * @param WP_User $user The user to decorate.
	 * @return User_Interface The user instance.
	 */
	public function get( WP_User $user ): User_Interface {
		if ( $this->instance && $user->ID === $this->instance->get_id() ) {
			return $this->instance;
		}

		/*
		 * Voluntarily checking `WP_user::$allcaps` instead of `WP_user::has_cap()` to avoid infinite loop with the `map_meta_cap` filter.
		 * Checking `manage_options` is appropriate because it allows both super admins and admins.
		 */
		if ( ! empty( $user->allcaps['manage_options'] ) ) {
			$this->instance = new NOOP( $user );

			return $this->instance;
		}

		$this->instance = new Translator( $user );

		return $this->instance;
	}
}
