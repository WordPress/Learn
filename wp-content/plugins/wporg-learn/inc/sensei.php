<?php

namespace WPOrg_Learn\Sensei;

use Exception;
use Sensei_Course, Sensei_Course_Enrolment_Manager;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'sensei_user_quiz_status', __NAMESPACE__ . '\quiz_status_message', 10, 2 );
add_action( 'template_redirect', __NAMESPACE__ . '\course_autoenrollment_from_quiz' );

/**
 * Modify the status message so that logging in takes precedence over enrolling in the course.
 *
 * This sets the stage for auto-enrolling in a course when a quiz page is visited while logged in.
 *
 * @param array $status
 * @param int   $lesson_id
 *
 * @return array
 */
function quiz_status_message( $status, $lesson_id ) {
	if ( ! is_user_logged_in() ) {
		$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );

		$status = array(
			'status' => 'login_required',
			'box_class' => 'info',
			'message' => sprintf(
				__( 'You must be <a href="%s">logged in</a> to take this quiz.', 'wporg-learn' ),
				wp_login_url( apply_filters( 'the_permalink', get_permalink( $quiz_id ), $quiz_id ) )
			),
			'extra' => '',
		);
	}

	return $status;
}

/**
 * Enroll a logged-in user in a course when they visit a quiz page.
 */
function course_autoenrollment_from_quiz() {
	if ( is_single() && 'quiz' === get_post_type() && is_user_logged_in() ) {
		$quiz = get_post();
		$lesson = get_post( $quiz->post_parent );
		$course_id = intval( $lesson->_lesson_course );
		$user_id = get_current_user_id();

		if ( $course_id && ! Sensei_Course::is_user_enrolled( $course_id, $user_id ) ) {
			$enrollment_manager = Sensei_Course_Enrolment_Manager::instance();

			try {
				$manual_enrollment  = $enrollment_manager->get_manual_enrolment_provider();
			} catch ( Exception $e ) {
				return;
			}

			$manual_enrollment->enrol_learner( $user_id, $course_id );
		}
	}
}
