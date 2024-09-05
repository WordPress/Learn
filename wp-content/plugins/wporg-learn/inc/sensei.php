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
add_action( 'init', __NAMESPACE__ . '\wporg_correct_sensei_slugs', 9 );
add_action( 'template_redirect', __NAMESPACE__ . '\restrict_my_courses_page_access' );
add_filter( 'sensei_login_url', __NAMESPACE__ . '\sensei_login_url', 20, 2 );
add_filter( 'sensei_registration_url', __NAMESPACE__ . '\sensei_registration_url', 20, 2 );
// Disable the Sensei user register page, use the WordPress registration page, see 'sensei_registration_url' filter.
add_filter( 'sensei_use_wp_register_link', '__return_true' );
// Repalce the Sensei login/register form contents.
add_action( 'sensei_login_form_before', __NAMESPACE__ . '\sensei_login_form_before' );
add_action( 'sensei_register_form_start', __NAMESPACE__ . '\sensei_register_form_start' );
// Disable Sensei user login & creation.
add_filter( 'init', __NAMESPACE__ . '\block_login_register_actions', 1 );

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
 * Redirect requests for the "My Courses" page to the login page and back, if logged out.
 */
function restrict_my_courses_page_access() {
	if ( ! is_user_logged_in() && is_page( Sensei()->settings->get_my_courses_page_id() ) ) {
		$redirect_to = wp_unslash( $_GET['redirect_to'] ?? '' ) ?: sensei_get_current_page_url();

		wp_redirect( wp_login_url( $redirect_to ) );
		exit;
	}
}

/**
 * Don't use the Sensei My Courses page as the login page.
 */
function sensei_login_url( $url, $redirect = '' ) {
	return wp_login_url( $redirect );
}

/**
 * Don't use the Sensei My Courses page as the registration page, but equally don't use the register page.
 *
 * Sensei uses the registration page for all logged out users, and the login page for all logged in users.
 * This is a poor user experience, as it means that the 'Take Course' links will direct to the registration page, rather than the login page.
 * For that reason, we're filtering the registration location to the login page.
 */
function sensei_registration_url( $url, $redirect = '' ) {
	return wp_login_url( $redirect );
}

/**
 * Replace the Sensei My Courses login form with a call to action to WordPress.org.
 */
function sensei_login_form_before() {
	// Start an output buffer, we'll remove the form content in the post-login-form filter.
	ob_start();

	add_action( 'sensei_login_form_after', function() {
		$html = ob_get_clean();

		/*
		 * Use the provided redirect_to, or the current page failing that.
		 * This differs from Sensei which doesn't respect the redirect_to parameter.
		 * Validation will occur by the login redirection code.
		 */
		$redirect_to = wp_unslash( $_GET['redirect_to'] ?? '' ) ?: sensei_get_current_page_url();

		// Replace the form with a call to action to WordPress.org.
		$html = preg_replace(
			'!<form.+</form>!is',
			sprintf(
				'<div class="wp-block-button"><a href="%s" class="wp-block-button__link wp-element-button button button-primary">%s</a></div>',
				esc_url( wp_login_url( $redirect_to ) ),
				__( 'Log In', 'wporg-learn' ),
			),
			$html
		);

		echo wp_kses_post( $html );
	} );
}

/**
 * Replace the Sensei registration form with a call to action to WordPress.org.
 */
function sensei_register_form_start() {
	// Start an output buffer, we'll replace the form content in the post-login-form filter.
	ob_start();

	add_action( 'sensei_register_form_end', function() {
		// We don't need any of the output buffer contents, since we're just in the <form> tag.
		ob_end_clean();

		// Output a registration button.
		echo sprintf(
			'<div class="wp-block-button"><a href="%s" class="wp-block-button__link wp-element-button button button-secondary">%s</a></div>',
			esc_url( wp_registration_url() ),
			esc_html__( 'Register', 'wporg-learn' ),
		);

		/*
		 * Add some custom styles for the My Courses page to remove the registation form border.
		 *
		 * This styles it to match the login section beside it.
		 */
		echo '<style>#my-courses #customer_login form { border: unset; margin: unset; padding: unset; } </style>';
	} );
}

/**
 * Forcibly disable Sensei user login & creation.
 *
 * Even if registrations are disabled, sensei still processes the form, for security we don't want this.
 */
function block_login_register_actions() {
	if ( function_exists( 'Sensei' ) ) {
		remove_action( 'init', array( Sensei()->frontend ?? false, 'sensei_process_registration' ), 2 );
		remove_filter( 'init', array( Sensei()->frontend ?? false, 'sensei_handle_login_request' ), 10 ); // Yes, Sensei calls it a filter.
	}

	// We're also going to forcefully disable the POST'd fields, incase the above action names change.

	// By unsetting this, it forces Sensei not to be able to create a user.
	unset( $_REQUEST['sensei_reg_password'], $_POST['sensei_reg_password'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// By unsetting these, sensei can't process a login.
	if ( 'sensei-login' == ( $_REQUEST['form'] ?? '' ) ) {
		unset( $_REQUEST['_wpnonce'], $_REQUEST['log'], $_REQUEST['pwd'], $_POST['log'], $_POST['pwd'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}

/**
 * Check if a lesson has a published course.
 *
 * @param int $lesson_id The ID of the lesson.
 * @return bool True if the lesson has a published course, false otherwise.
 */
function get_lesson_has_published_course( $lesson_id ) {
	$course_id = get_post_meta( $lesson_id, '_lesson_course', true );
	$course_status = get_post_status( $course_id );

	return ! empty( $course_id ) && 'publish' === $course_status;
}
