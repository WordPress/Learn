<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Everyone class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to Everyone" visibility type.
 */
class Everyone extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'EVERYONE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Everyone', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Visible', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is visible to everyone.', 'sensei-pro' );
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		return true;
	}
}
