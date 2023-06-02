<?php
/**
 * File containing the Student_No_Progress_Email_Generator class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_WC_Paid_Courses\Emails\Generators;

use Sensei\Internal\Emails\Email_Repository;
use Sensei\Internal\Emails\Generators\Email_Generators_Abstract;
use Sensei_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Student_No_Progress_Email_Generator.
 *
 * @internal
 *
 * @since 1.12.0
 */
class Student_No_Progress_Email_Generator extends Email_Generators_Abstract {

	/**
	 * Number of days without progress.
	 *
	 * @var int
	 */
	private $days_without_progress;

	/**
	 * Student_No_Progress_Email_Generator constructor.
	 *
	 * @internal
	 * @since 1.12.0
	 *
	 * @param Email_Repository $email_repository      Instance of email repository.
	 * @param int              $days_without_progress Number of days without progress.
	 */
	public function __construct( $email_repository, $days_without_progress ) {
		parent::__construct( $email_repository );
		$this->days_without_progress = $days_without_progress;
		add_filter( 'sensei_email_is_available', [ $this, 'maybe_make_email_available' ], 10, 2 );
	}

	/**
	 * Initialize the email hooks.
	 *
	 * @internal
	 * @access public
	 * @since 1.12.0
	 *
	 * @return void
	 */
	public function init() {
		add_action(
			'sensei_wc_paid_courses_student_no_progress_reminder',
			[ $this, 'send_student_no_progress_email' ],
			10,
			3
		);
	}

	/**
	 * Make email available if it is a no progress email.
	 *
	 * @internal
	 * @access private
	 *
	 * @param bool     $is_available Whether the email is available.
	 * @param \WP_Post $email        Email.
	 * @return bool Whether the email is available.
	 */
	public function maybe_make_email_available( $is_available, $email ) {
		return $is_available ||
			$this->get_identifier() === get_post_meta( $email->ID, '_sensei_email_identifier', true );
	}

	/**
	 * Send email to student to notify about forgotten course.
	 *
	 * @access private
	 *
	 * @param int $course_id             The course ID.
	 * @param int $student_id            The student ID.
	 * @param int $days_without_progress Number of days without progress.
	 */
	public function send_student_no_progress_email( $course_id, $student_id, $days_without_progress ) {
		if ( $days_without_progress !== $this->days_without_progress ) {
			return;
		}

		$course = get_post( $course_id );
		if ( ! $course || 'publish' !== $course->post_status ) {
			return;
		}

		$target_post_id = $this->get_target_page_post_id_for_continue_url( $course_id, $student_id );
		$lesson         = get_post( $target_post_id );
		if ( ! $lesson || 'publish' !== $lesson->post_status ) {
			return;
		}

		$student    = new \WP_User( $student_id );
		$recipient  = stripslashes( $student->user_email );
		$resume_url = esc_url( get_permalink( absint( $target_post_id ?? $course_id ) ) );

		$this->send_email_action(
			[
				$recipient => [
					'student:displayname' => $student->display_name,
					'course:name'         => $course->post_title,
					'lesson:name'         => $lesson->post_title,
					'resume:url'          => $resume_url,
					'day:count'           => $days_without_progress,
				],
			]
		);
	}

	/**
	 * Get the email identifier.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function get_identifier() {
		/**
		 * Filters the identifiers for no progress mails.
		 *
		 * @since 1.12.0
		 *
		 * @hook sensei_wc_paid_courses_student_no_progress_email_identifiers
		 *
		 * @param {array} $identifiers Email identifiers for no progress email.
		 *
		 * @return {array} Filtered email identifiers for no progress email.
		 */
		$identifiers = apply_filters(
			'sensei_wc_paid_courses_student_no_progress_email_identifiers',
			[
				3  => 'student_no_progress_3_days',
				7  => 'student_no_progress_7_days',
				28 => 'student_no_progress_28_days',
			]
		);

		return $identifiers[ $this->days_without_progress ];
	}

	/**
	 * Gets the id for the last lesson the user was working on, or the next lesson, or
	 * the course id as fallback for fresh users or courses with no lessons.
	 *
	 * @param int $course_id Id of the course.
	 * @param int $user_id   Id of the user.
	 * @return int
	 */
	private function get_target_page_post_id_for_continue_url( $course_id, $user_id ) {
		$course_lessons = Sensei()->course->course_lessons( $course_id, 'publish', 'ids' );
		if ( empty( $course_lessons ) ) {
			return $course_id;
		}

		// First try to get the lesson the user started or updated last.
		$activity_args = [
			'post__in' => $course_lessons,
			'user_id'  => $user_id,
			'type'     => 'sensei_lesson_status',
			'number'   => 1,
			'orderby'  => 'comment_date',
			'order'    => 'DESC',
			'status'   => [ 'in-progress', 'ungraded' ],
		];

		$last_lesson_activity = Sensei_Utils::sensei_check_for_activity( $activity_args, true );
		if ( ! empty( $last_lesson_activity ) ) {
			return $last_lesson_activity->comment_post_ID;
		}

		// If there is no such lesson, get the first lesson that the user has not yet started.
		$completed_lessons     = Sensei()->course->get_completed_lesson_ids( $course_id, $user_id );
		$not_completed_lessons = array_diff( $course_lessons, $completed_lessons );
		if ( count( $course_lessons ) !== count( $not_completed_lessons ) && ! empty( $not_completed_lessons ) ) {
			return current( $not_completed_lessons );
		}

		return $course_id;
	}
}
