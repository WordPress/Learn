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
add_action( 'init', __NAMESPACE__ . '\wporg_correct_sensei_slugs' );

/**
 * Slugs in Sensei are translatable, which won't work for our site and the language switcher.
 *
 * This resets all slugs to their default values, regardless of what the translation comes up with.
 *
 * @return void
 */
function wporg_correct_sensei_slugs() {
	add_filter( 'sensei_course_slug', function () {
		return 'course';
	} );
	add_filter( 'sensei_lesson_slug', function () {
		return 'lesson';
	} );
	add_filter( 'sensei_quiz_slug', function () {
		return 'quiz';
	} );
	add_filter( 'sensei_question_slug', function () {
		return 'question';
	} );
	add_filter( 'sensei_multiple_question_slug', function () {
		return 'multiple_question';
	} );
	add_filter( 'sensei_messages_slug', function () {
		return 'messages';
	} );
	add_filter( 'sensei_course_category_slug', function () {
		return 'course-category';
	} );
	add_filter( 'sensei_quiz_type_slug', function () {
		return 'quiz-type';
	} );
	add_filter( 'sensei_question_type_slug', function () {
		return 'question-type';
	} );
	add_filter( 'sensei_question_category_slug', function () {
		return 'question-category';
	} );
	add_filter( 'sensei_lesson_tag_slug', function () {
		return 'lesson-tag';
	} );
}

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

/**
 * Remove header spacing in Learning Mode UI
 */
function wporg_fix_learning_mode_header_space() {
	wp_register_style( 'learning-mode-header-fix', false, array(), '1.0' );
	wp_enqueue_style( 'learning-mode-header-fix' );

	$custom_styles = '
		html {
			--wp-global-header-height: 0 !important;
		}
	';

	wp_add_inline_style( 'learning-mode-header-fix', $custom_styles );
}
add_action( 'sensei_course_learning_mode_load_theme', __NAMESPACE__ . '\wporg_fix_learning_mode_header_space' );
