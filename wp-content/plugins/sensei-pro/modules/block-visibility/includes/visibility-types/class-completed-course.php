<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Completed_Course class.
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
 * Class that handles the "Visible to students who completed the current course" visibility type.
 */
class Completed_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'COMPLETED_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Completed course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Course completed', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is only visible to users that have completed this course.', 'sensei-pro' );
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		$course_id       = Sensei_Utils::get_current_course();
		$current_user_id = get_current_user_id();

		// If there is no course id or user id then can't view the block.
		if ( ! $course_id || ! $current_user_id ) {
			return false;
		}
		$course = get_post( $course_id );
		return Sensei_Utils::user_completed_course( $course, $current_user_id );
	}
}
