<?php
/**
 * File containing the class \Sensei_Pro\Course_Helper.
 *
 * @package sensei-pro
 * @since 1.6.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that provides course helpers methods.
 *
 * @class Sensei_Pro\Course_Helper
 */
class Course_Helper {

	/**
	 * Get a course id or null or bool from a current page.
	 *
	 * @access public
	 * @since 1.6.0
	 */
	public static function get_course_id_for_current_page() {
		if ( is_singular( 'course' ) ) {
			return get_the_ID();
		}

		if ( is_singular( 'lesson' ) ) {
			return Sensei()->lesson->get_course_id( get_the_ID() );
		}

		if ( is_singular( 'quiz' ) ) {
			$lesson_id = Sensei()->quiz->get_lesson_id( get_the_ID() );

			return (int) Sensei()->lesson->get_course_id( $lesson_id );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( is_tax( 'module' ) && ! empty( $_GET['course_id'] ) && is_numeric( (int) $_GET['course_id'] ) ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- No form is submitted and argument is validated to be a number.
			return (int) $_GET['course_id'];
		}
		return null;
	}

}
