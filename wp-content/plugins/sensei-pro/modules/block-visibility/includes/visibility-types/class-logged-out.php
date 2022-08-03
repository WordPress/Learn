<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Logged_Out class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to logged out users" visibility type.
 */
class Logged_Out extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'LOGGED_OUT';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Logged out users only', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Logged out', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is shown only to users that are not logged in.', 'sensei-pro' );
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		return ! is_user_logged_in();
	}
}
