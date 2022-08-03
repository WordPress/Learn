<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Not_Completed_Lesson class.
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
 * Class that handles the "Visibile to users that not completed the lesson" visibility type.
 */
class Not_Completed_Lesson extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'NOT_COMPLETED_LESSON';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Users that did not complete lesson', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Lesson not completed', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is visible to enrolled users that have not completed the lesson.', 'sensei-pro' );
	}

	/**
	 * Instance of Completed_Lesson visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Completed_Lesson
	 */
	private $completed_lesson_type;

	/**
	 * Instance of Enrolled_To_Course visibility type.
	 *
	 * @var Sensei_Pro_Block_Visibility\Types\Enrolled_To_Course
	 */
	private $enrolled_to_course_type;

	/**
	 * Constructor method for \Sensei_Pro_Block_Visibility\Types\Type\Not_Completed_Lesson.
	 */
	public function __construct() {
		$this->completed_lesson_type   = new Completed_Lesson();
		$this->enrolled_to_course_type = new Enrolled_To_Course();
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		return $this->enrolled_to_course_type->is_visible( $visibility_settings ) && ! $this->completed_lesson_type->is_visible( $visibility_settings );
	}
}
