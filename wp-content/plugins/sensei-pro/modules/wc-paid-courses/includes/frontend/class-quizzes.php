<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Frontend\Quizzes.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses\Frontend;

use Sensei_Utils;
use Sensei_WC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin functionality related to quizzes.
 *
 * @deprecated 2.0.0
 *
 * @class Sensei_WC_Paid_Courses\Frontend\Quizzes
 */
final class Quizzes {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Lessons constructor. Prevents other instances from being created outside of `Quizzes::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions related to the frontend.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 */
	public function init() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Filter the course sign up notice message on the quiz page.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string $message     Message to show for the course sign up notice.
	 * @param int    $course_id   Post ID for the course.
	 * @param string $course_link Generated HTML link to the course.
	 * @return string
	 */
	public function course_signup_notice_message( $message, $course_id, $course_link ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $message;
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
