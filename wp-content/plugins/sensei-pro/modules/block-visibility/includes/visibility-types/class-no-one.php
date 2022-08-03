<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\No_One class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to no one" visibility type.
 */
class No_One extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'NO_ONE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Hide block for everyone', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Hidden', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is hidden from everyone.', 'sensei-pro' );
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		return false;
	}
}
