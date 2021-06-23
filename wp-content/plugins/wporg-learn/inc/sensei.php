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
add_action( 'sensei_single_course_content_inside_after', __NAMESPACE__ . '\remove_single_course_lessons_title', 1 );
add_filter( 'sensei_load_default_supported_theme_wrappers', '__return_false' );
add_action( 'sensei_before_main_content', __NAMESPACE__ . '\theme_wrapper_start' );
add_action( 'sensei_after_main_content', __NAMESPACE__ . '\theme_wrapper_end' );

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
 * Remove the "Lessons" heading on a single course page.
 *
 * @return void
 */
function remove_single_course_lessons_title() {
	if ( is_singular( 'course' ) ) {
		remove_action( 'sensei_single_course_content_inside_after', array( 'Sensei_Course', 'the_course_lessons_title' ), 9 );
	}
}

/**
 * Define a content wrapper opening for frontend Sensei views.
 */
function theme_wrapper_start() {
	?>
	<main id="main" class="site-main type-page" role="main">
	<?php get_template_part( 'template-parts/component', 'breadcrumbs' ); ?>
		<div id="main-content">
	<?php
}

/**
 * Define a content wrapper closing for frontend Sensei views.
 */
function theme_wrapper_end() {
	?>
		</div>
	</main>
	<?php
}
