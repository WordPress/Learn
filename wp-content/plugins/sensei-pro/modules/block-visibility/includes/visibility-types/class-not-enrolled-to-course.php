<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Not_Enrolled_To_Course class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

use Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to students that have not enrolled to the current course" visibility type.
 */
class Not_Enrolled_To_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'NOT_ENROLLED_TO_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Users not enrolled in this course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Not enrolled', 'sensei-pro' );
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
			__( 'Block is visible to users that are not enrolled to the course%s.', 'sensei-pro' ),
			$lesson_notice
		);
	}

	/**
	 * Instance of Enrolled_To_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course
	 */
	private $enrolled_to_course_type;

	/**
	 * Constructor method for \Sensei_Pro_Block_Visibility\Types\Type\Not_Enrolled_To_Course.
	 */
	public function __construct() {
		$this->enrolled_to_course_type = new Enrolled_To_Course();
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		return ! $this->enrolled_to_course_type->is_visible( $visibility_settings );
	}
}
