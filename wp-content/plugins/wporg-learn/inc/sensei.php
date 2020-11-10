<?php

namespace WPOrg_Learn\Sensei;

use Exception;
use Sensei_Course, Sensei_Lesson, Sensei_Course_Enrolment_Manager;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'sensei_user_quiz_status', __NAMESPACE__ . '\quiz_status_message', 10, 2 );
add_action( 'template_redirect', __NAMESPACE__ . '\course_autoenrollment_from_quiz' );
add_action( 'sensei_single_quiz_content_inside_before', __NAMESPACE__ . '\prepend_lesson_content_to_quiz', 100 );
add_action( 'sensei_pagination', __NAMESPACE__ . '\remove_quiz_pagination_breadcrumb', 1 );
add_action( 'template_redirect', __NAMESPACE__ . '\redirect_lesson_to_quiz' );

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
 *
 * @return void
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

/**
 * Add a quiz's lesson content to the top of the quiz page.
 *
 * @param int $quiz_id
 *
 * @return void
 */
function prepend_lesson_content_to_quiz( $quiz_id ) {
	$quiz = get_post( $quiz_id );
	$lesson_id = $quiz->post_parent;

	setup_postdata( $lesson_id );

	echo '<section class="entry fix">';
	if ( apply_filters( 'sensei_video_position', 'top', $lesson_id ) == 'top' ) {
		do_action( 'sensei_lesson_video', $lesson_id );
	}
	the_content();
	echo '</section>';

	wp_reset_postdata();
}

/**
 * Remove the breadcrumb that leads back to the lesson from the footer of quiz pages.
 *
 * @return void
 */
function remove_quiz_pagination_breadcrumb() {
	if ( is_single() && 'quiz' === get_post_type() ) {
		remove_action( 'sensei_pagination', array( Sensei()->frontend, 'sensei_breadcrumb' ), 80 );
	}
}

/**
 * Redirect lessons to their quizzes if there are questions.
 *
 * @return void
 */
function redirect_lesson_to_quiz() {
	if ( is_single() && 'lesson' === get_post_type() ) {
		$lesson_id = get_the_ID();

		if ( Sensei_Lesson::lesson_quiz_has_questions( $lesson_id ) ) {
			$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson_id );
			$quiz_permalink = get_permalink( $quiz_id );
			wp_safe_redirect( $quiz_permalink );
		}
	}
}
