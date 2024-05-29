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
add_filter( 'pre_render_block', __NAMESPACE__ . '\modify_course_query', 10, 2 );
add_filter( 'rest_course_query', __NAMESPACE__ . '\modify_course_rest_query', 10, 2 );

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

/**
 * Format a date query var into a DateTime object.
 */
function wporg_learn_get_date( $query_var ) {
	$date = sanitize_text_field( $_GET[ $query_var ] ?? '' );

	return \DateTime::createFromFormat( 'Y-m-d', $date ?? '', new \DateTimeZone( 'UTC' ) );
}

/**
 * Get the number of unique learners between two dates.
 *
 * @param \DateTime $from_date
 * @param \DateTime $to_date
 *
 * @return int
 */
function wporg_learn_get_student_count( $from_date, $to_date ) {

	if ( ! $from_date || ! $to_date ) {
		return 0;
	}

	global $wpdb;

	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT user_id) FROM $wpdb->comments as c
            INNER JOIN $wpdb->commentmeta AS cm ON c.comment_ID = cm.comment_id
			WHERE c.comment_type = 'sensei_course_status'
            AND cm.meta_key = 'start'
            AND cm.meta_value >= %s
            AND cm.meta_value <= %s",
			array(
				$from_date->format( 'Y-m-d H:i:s' ),
				$to_date->format( 'Y-m-d H:i:s' ),
			)
		)
	);
}

/**
 * Add script to count unique learners
 */
function wporg_learn_add_student_count_to_reports( $type ) {
	if ( 'users' !== $type ) {
		return; // Only show the count on the students report screen.
	}

	$from_date = wporg_learn_get_date( 'from_date' );
	$to_date   = wporg_learn_get_date( 'to_date' );

	$student_count = wporg_learn_get_student_count( $from_date, $to_date );

	?>
	<div class="actions bulkactions">
		<label><?php esc_html_e( 'Total number of students', 'wporg-learn' ); ?></label>
		<input
				class="sensei-date-picker"
				name="from_date"
				type="text"
				autocomplete="off"
				placeholder="<?php echo esc_attr( __( 'From Date', 'wporg-learn' ) ); ?>"
				value="<?php echo esc_attr( $from_date ? $from_date->format( 'Y-m-d' ) : '' ); ?>"
		/>
		<input
				class="sensei-date-picker"
				name="to_date"
				type="text"
				autocomplete="off"
				placeholder="<?php echo esc_attr( __( 'To Date', 'wporg-learn' ) ); ?>"
				value="<?php echo esc_attr( $to_date ? $to_date->format( 'Y-m-d' ) : '' ); ?>"
		/>
		<label>: <?php echo (int) $student_count; ?></label>
	</div>
	<br>
	<?php
}
add_action( 'sensei_reports_overview_before_top_filters', __NAMESPACE__ . '\wporg_learn_add_student_count_to_reports' );

/**
 * Get the URL of the "My Courses" page.
 *
 * @return string The URL of the "My Courses" page.
 */
function get_my_courses_page_url() {
	$page_id = Sensei()->settings->get_my_courses_page_id();

	if ( ! $page_id ) {
		return '';
	}

	return get_permalink( $page_id );
}

/**
 * Modify the course query to add the featured course meta query if set.
 *
 * @param mixed $pre_render The pre-render value.
 * @param mixed $parsed_block The parsed block value.
 * @return mixed The modified course query.
 */
function modify_course_query( $pre_render, $parsed_block ) {
	if ( isset( $parsed_block['attrs']['namespace'] ) && 'wporg-learn/course-grid' === $parsed_block['attrs']['namespace']
	) {
		add_filter(
			'query_loop_block_query_vars',
			function( $query, $block ) use ( $parsed_block ) {
				if ( 'course' !== $query['post_type'] || ! isset( $parsed_block['attrs']['query']['courseFeatured'] ) ) {
					return $query;
				}

				$course_featured = $parsed_block['attrs']['query']['courseFeatured'];

				if ( true === $course_featured ) {
					$query['meta_key']   = '_course_featured';
					$query['meta_value'] = 'featured';
				}

				return $query;
			},
			10,
			2
		);
	}

	return $pre_render;
}

/**
 * Modify the course REST query to add the featured course meta query if set.
 *
 * @param array           $args The query arguments.
 * @param WP_REST_Request $request The REST request object.
 * @return array The modified query arguments.
 */
function modify_course_rest_query( $args, $request ) {
	$course_featured = $request->get_param( 'courseFeatured' );

	if ( 'true' === $course_featured ) {
		$args['meta_query'][] = array(
			'key'     => '_course_featured',
			'value'   => 'featured',
			'compare' => '=',
		);
	}

	return $args;
}
