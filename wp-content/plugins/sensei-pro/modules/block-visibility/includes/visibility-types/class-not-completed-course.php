<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Not_Completed_Course class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Block_Visibility\Types\Completed_Course;
use Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course;

/**
 * Class that handles the "Visible to students that have not completed the current course" visibility type.
 */
class Not_Completed_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'NOT_COMPLETED_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Users that did not complete course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Course not completed', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is visible to enrolled users that have not completed the course.', 'sensei-pro' );
	}

	/**
	 * Instance of Completed_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Completed_Course
	 */
	private $completed_course_type;

	/**
	 * Instance of Enrolled_To_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course
	 */
	private $enrolled_to_course_type;

	/**
	 * Constructor method for \Sensei_Pro_Block_Visibility\Types\Type\Not_Completed_Course.
	 */
	public function __construct() {
		$this->completed_course_type   = new Completed_Course();
		$this->enrolled_to_course_type = new Enrolled_To_Course();
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		return $this->enrolled_to_course_type->is_visible( $visibility_settings ) && ! $this->completed_course_type->is_visible( $visibility_settings );
	}

}
