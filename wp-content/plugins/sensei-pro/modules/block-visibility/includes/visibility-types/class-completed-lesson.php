<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Completed_Lesson class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

use Sensei_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to users that completed the lesson" visibility type.
 */
class Completed_Lesson extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'COMPLETED_LESSON';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Users that completed lesson', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Lesson completed', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is visible to users that completed the lesson.', 'sensei-pro' );
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		$lesson_id = Sensei_Utils::get_current_lesson();
		return Sensei_Utils::user_completed_lesson( $lesson_id );
	}
}
