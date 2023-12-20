<?php
/**
 * File containing the Course_Expiration_Email_Generator class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro_Course_Expiration\Emails\Generators;

use Sensei\Internal\Emails\Email_Repository;
use Sensei\Internal\Emails\Generators\Email_Generators_Abstract;
use Sensei_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Course_Expiration_Email_Generator
 *
 * @internal
 *
 * @since 1.12.0
 */
class Course_Expiration_Email_Generator extends Email_Generators_Abstract {

	/**
	 * Count of remaining days.
	 *
	 * @var int
	 */
	private $remaining_days;

	/**
	 * Identifier used in usage tracking.
	 *
	 * @var string
	 */
	const USAGE_TRACKING_TYPE = 'learner-course-expiration';

	/**
	 * Course_Expiration_Email_Generator constructor.
	 *
	 * @param Email_Repository $email_repository Instance of email repository.
	 * @param int              $remaining_days   Count of remaining days.
	 *
	 * @since 1.12.0
	 *
	 * @internal
	 */
	public function __construct( $email_repository, $remaining_days ) {
		parent::__construct( $email_repository );
		$this->remaining_days = $remaining_days;
		add_filter( 'sensei_email_is_available', [ $this, 'maybe_make_email_available' ], 10, 2 );
	}

	/**
	 * Initialize the email hooks.
	 *
	 * @access public
	 * @since 1.12.0
	 *
	 * @return void
	 */
	public function init() {
		// Support for older versions of Sensei.
		if ( method_exists( $this, 'maybe_add_action' ) ) {
			$this->maybe_add_action( "sensei_pro_course_expiration_{$this->remaining_days}_days_mail", [ $this, 'send_course_expiration_mail' ], 10, 2 );
		} else {
			add_action( "sensei_pro_course_expiration_{$this->remaining_days}_days_mail", [ $this, 'send_course_expiration_mail' ], 10, 2 );
		}
	}

	/**
	 * Make email available if it is a course expiration email.
	 *
	 * @param bool     $is_available Whether the email is available.
	 * @param \WP_Post $email        Email identifier.
	 *
	 * @internal
	 *
	 * @return bool Whether the email is available.
	 */
	public function maybe_make_email_available( $is_available, $email ) {
		return $is_available ||
			$this->get_identifier() === get_post_meta( $email->ID, '_sensei_email_identifier', true );
	}

	/**
	 * Send email to student to notify about course expiration.
	 *
	 * @param int $student_id The student ID.
	 * @param int $course_id  The course ID.
	 *
	 * @access private
	 */
	public function send_course_expiration_mail( $student_id, $course_id ) {

		$course = get_post( $course_id );

		if ( ! $course || 'publish' !== $course->post_status ) {
			return;
		}

		$student        = new \WP_User( $student_id );
		$recipient      = stripslashes( $student->user_email );
		$target_post_id = $this->get_target_page_post_id_for_continue_url( $course_id, $student_id );
		$resume_url     = esc_url( get_permalink( absint( $target_post_id ?? $course_id ) ) );

		$expiration_date           = current_datetime()->modify( "+{$this->remaining_days} day" );
		$formatted_expiration_date = $expiration_date->format( __( 'l, F d, Y', 'sensei-pro' ) );
		$date_text                 = 0 === $this->remaining_days ? __( 'today', 'sensei-pro' ) : $formatted_expiration_date;

			$this->send_email_action(
				[
					$recipient => [
						'student:displayname' => $student->display_name,
						'course:name'         => $course->post_title,
						'resume:url'          => $resume_url,
						'date:dtext'          => $date_text,
						'day:count'           => $this->remaining_days,
					],
				]
			);
	}

	/**
	 * Get the email identifier.
	 *
	 * @return string
	 */
	public function get_identifier() {
		/**
		 * Filters the identifiers for course expiration mails.
		 *
		 * @since 1.12.0
		 *
		 * @hook sensei_pro_course_expiration_email_identifiers
		 *
		 * @param {array} $identifiers Email identifiers for course expiration.
		 *
		 * @return {array} Filtered email identifiers for course expiration.
		 */
		$identifiers = apply_filters(
			'sensei_pro_course_expiration_email_identifiers',
			[
				0 => 'course_expiration_today',
				3 => 'course_expiration_3_days',
				7 => 'course_expiration_7_days',
			]
		);

		return $identifiers[ $this->remaining_days ];
	}

	/**
	 * Gets the id for the last lesson the user was working on, or the next lesson, or
	 * the course id as fallback for fresh users or courses with no lessons.
	 *
	 * @access private
	 *
	 * @param int $course_id Id of the course.
	 * @param int $user_id   Id of the user.
	 *
	 * @return int
	 */
	public function get_target_page_post_id_for_continue_url( $course_id, $user_id ) {
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
		} else {
			// If there is no such lesson, get the first lesson that the user has not yet started.
			$completed_lessons     = Sensei()->course->get_completed_lesson_ids( $course_id, $user_id );
			$not_completed_lessons = array_diff( $course_lessons, $completed_lessons );

			if ( count( $course_lessons ) !== count( $not_completed_lessons ) && ! empty( $not_completed_lessons ) ) {
				return current( $not_completed_lessons );
			}
		}
		return $course_id;
	}
}
