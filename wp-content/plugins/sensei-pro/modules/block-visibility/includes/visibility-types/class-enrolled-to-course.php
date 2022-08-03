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
		return __( 'Users enrolled in this course', 'sensei-pro' );
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
		global $pagenow;
		$lesson_notice = '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) === 'lesson' ) {
				$lesson_notice = __( ' (Only applies to preview lessons)', 'sensei-pro' );
			}
		}
		return sprintf(
		/* translators: %s: A notice that this option can only be used in preview lessons */
			__( 'Block is visible to users that are enrolled to the course%s.', 'sensei-pro' ),
			$lesson_notice
		);
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
