<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Not_Enrolled_To_Any_Course class.
 *
 * @package sensei-pro
 * @since 1.10.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to users that are not enrolled to a course" visibility type.
 */
class Not_Enrolled_To_Any_Course extends Type {
	/**
	 * Instance of Enrolled_To_Any_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Enrolled_To_Any_Course
	 */
	private $enrolled_to_any_course_type;

	/**
	 * Constructor ofr the Sensei_Pro_Block_Visibility\Types\Not_Enrolled_To_Any_Course class.
	 */
	public function __construct() {
		$this->enrolled_to_any_course_type = new Enrolled_To_Any_Course();
	}

	/**
	 * Name
	 */
	public function name(): string {
		return 'NOT_ENROLLED_TO_ANY_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Students that are not enrolled to any course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Not enrolled to any course', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'This block is visible only to students not enrolled in any course.', 'sensei-pro' );
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		return ! $this->enrolled_to_any_course_type->is_visible( $visibility_settings );
	}
}
