<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Not_Completed_Any_Course class.
 *
 * @package sensei-pro
 * @since 1.10.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to users that didn't complete a course" visibility type.
 */
class Not_Completed_Any_Course extends Type {
	/**
	 * Instance of Completed_Any_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Completed_Any_Course
	 */
	private $completed_any_course_type;

	/**
	 * Constructor ofr the Sensei_Pro_Block_Visibility\Types\Not_Completed_Any_Course class.
	 */
	public function __construct() {
		$this->completed_any_course_type = new Completed_Any_Course();
	}

	/**
	 * Name
	 */
	public function name(): string {
		return 'NOT_COMPLETED_ANY_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Students that have never completed a course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Students that have never completed a course', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'This block is visible only to students that have not completed any course.', 'sensei-pro' );
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		return ! $this->completed_any_course_type->is_visible( $visibility_settings );
	}
}
