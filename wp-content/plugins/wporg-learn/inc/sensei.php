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
// Add custom meta box and save for lesson module selection without selecting a course.
add_action( 'add_meta_boxes', __NAMESPACE__ . '\maybe_customize_sensei_module_metabox', 20, 2 );
add_action( 'save_post', __NAMESPACE__ . '\maybe_customize_save_lesson_module', 9, 3 );

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
 * Replace the Sensei module selector with one that allows selecting any module,
 * if the lesson is not associated with a course.
 *
 * @param string  $post_type
 * @param WP_Post $post
 *
 * @return void
 */
function maybe_customize_sensei_module_metabox( $post_type, $post ) {
	$course_id = (int) get_post_meta( $post->ID, '_lesson_course', true );

	if ( ! $course_id ) {
		remove_meta_box( 'module_select', 'lesson', 'side' );

		add_meta_box( 'module_select', __( 'Module', 'sensei-lms' ), __NAMESPACE__ . '\output_lesson_module_metabox', 'lesson', 'side', 'default' );
	}
}

/**
 * Get the lesson module if it Exists. Defaults to 0 if none found.
 *
 * @param WP_Post $post The post.
 * @return int
 */
function get_lesson_module_if_exists( $post ) {
	// Get existing lesson module.
	$lesson_module      = 0;
	$lesson_module_list = wp_get_post_terms( $post->ID, 'module' );
	if ( is_array( $lesson_module_list ) && count( $lesson_module_list ) > 0 ) {
		foreach ( $lesson_module_list as $single_module ) {
			$lesson_module = $single_module->term_id;
			break;
		}
	}
	return $lesson_module;
}

/**
 * Outputs the lesson module meta box HTML.
 *
 * @param WP_Post $lesson_post The lesson post object.
 */
function output_lesson_module_metabox( $lesson_post ) {
	// Get current lesson module.
	$module_id = get_lesson_module_if_exists( $lesson_post );

	$html  = '<div id="lesson-module-metabox-select">';
	$html .= render_lesson_module_select_for_course( $module_id );
	$html .= '</div>';

	echo wp_kses(
		$html,
		array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'input'  => array(
					'id'    => array(),
					'name'  => array(),
					'type'  => array(),
					'value' => array(),
				),
				'option' => array(
					'selected' => array(),
					'value'    => array(),
				),
				'select' => array(
					'class' => array(),
					'id'    => array(),
					'name'  => array(),
					'style' => array(),
				),
			)
		)
	);
}

/**
 * Renders the lesson module select input, with all modules as options.
 *
 * @param int|null $current_module_id The currently selected module post ID.
 *
 * @return string The lesson module select HTML.
 */
function render_lesson_module_select_for_course( int $current_module_id = null ): string {
	$modules = get_terms(
		array(
			'taxonomy'   => 'module',
			'hide_empty' => false,
		)
	);

	// Build the HTML.
	$input_name = 'lesson_module';

	$html  = '';
	$html .= '<input type="hidden" name="' . esc_attr( 'wporg_lesson_module_nonce' ) . '" id="' . esc_attr( 'wporg_lesson_module_nonce' ) . '" value="' . esc_attr( wp_create_nonce( 'wporg_module_select' ) ) . '" />';

	if ( $modules ) {
		$html .= '<select id="lesson-module-options" name="' . esc_attr( $input_name ) . '" class="widefat" style="width: 100%">' . "\n";
		$html .= '<option value="">' . esc_html__( 'None', 'sensei-lms' ) . '</option>';
		foreach ( $modules as $module ) {
			$html .= '<option value="' . esc_attr( absint( $module->term_id ) ) . '"' . selected( $module->term_id, $current_module_id, false ) . '>' . esc_html( $module->name ) . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";
	} else {
		$html .= '<p>' . esc_html__( 'No modules found.', 'wporg-learn' ) . '</p>';
	}

	return $html;
}

/**
 * Checks if the post is a lesson without a course id, and if so removes the Sensei save action,
 * then runs our save which allows adding the lesson to a module without a course set.
 *
 * @param integer $post_id ID of post.
 * @param WP_Post $post Post object.
 * @return void
 */
function maybe_customize_save_lesson_module( $post_id, $post ) {
	global $wp_filter;

	if ( 'lesson' !== $post->post_type ) {
		return;
	}

	$course_id = (int) get_post_meta( $post_id, '_lesson_course', true );

	if ( ! $course_id && isset( $wp_filter['save_post'] ) ) {
		foreach ( $wp_filter['save_post']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $id => $callback ) {
				if ( is_array( $callback['function'] ) &&
					is_object( $callback['function'][0] ) &&
					method_exists( $callback['function'][0], 'save_lesson_module' ) ) {
					remove_action( 'save_post', $callback['function'], $priority );

					wporg_save_lesson_module( $post_id, $post );

					return;
				}
			}
		}
	}
}

/**
 * Save module to lesson. This method checks for authorization, and checks the incoming nonce.
 *
 * @param  integer $post_id ID of post.
 * @param WP_Post $post Post object.
 * @return mixed            Post ID on permissions failure, boolean true on success
 */
function wporg_save_lesson_module( $post_id, $post ) {
	// Verify post type and nonce
	if ( ( get_post_type( $post ) != 'lesson' ) || ! isset( $_POST['wporg_lesson_module_nonce'] )
		|| ! wp_verify_nonce( $_POST['wporg_lesson_module_nonce'], 'wporg_module_select' ) ) {
		return $post_id;
	}

	// Check if user has permissions to edit lessons
	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Check if user has permissions to edit this specific post
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Get module ID
	$lesson_module_id_key = 'lesson_module';
	$module_id            = isset( $_POST[ $lesson_module_id_key ] ) ? $_POST[ $lesson_module_id_key ] : null;

	// Set the module on the lesson
	set_module( $module_id, $post_id );

	return true;
}

/**
 * Set the module on the lesson in the DB.
 *
 * If the module is not associated with the course that the lesson belongs
 * to, the lesson's module will instead be unset. The third argument may be
 * used to change which course to check against. This is useful when the
 * course and module are being updated at the same time.
 *
 * @param integer|string $module_id ID of the new module.
 */
function set_module( $module_id, $lesson_id ) {
	$modules_taxonomy = Sensei()->modules->taxonomy;

	// Convert IDs to integers
	if ( $module_id || ! empty( $module_id ) ) {
		$module_id = intval( $module_id );
	}

	// Check if the lesson is already assigned to a module.
	// Modules and lessons have 1 -> 1 relationship.
	// We delete existing module term relationships for this lesson if no module is selected
	if ( ! $module_id || empty( $module_id ) ) {
		wp_delete_object_term_relationships( $lesson_id, $modules_taxonomy );
		return;
	}

	// Assign lesson to selected module
	wp_set_object_terms( $lesson_id, $module_id, $modules_taxonomy, false );

	// Set default order for lesson inside module
	$order_module_key = '_order_module_' . $module_id;
	if ( ! get_post_meta( $lesson_id, $order_module_key, true ) ) {
		update_post_meta( $lesson_id, $order_module_key, 0 );
	}
}
