<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course class.
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
 * Class that handles the "Visible to users that are enrolled to the current course" visibility type.
 */
class Enrolled_To_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'ENROLLED_TO_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Enrolled students', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Enrolled', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'If this lesson can be previewed, this block is visible only to enrolled students in this course.', 'sensei-pro' );
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

		return Sensei()->course->is_user_enrolled( $course_id, $current_user_id );
	}
}
