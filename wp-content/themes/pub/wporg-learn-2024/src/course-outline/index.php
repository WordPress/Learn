<?php

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	wp_enqueue_script(
		'wporg-learn-2024-course-outline',
		get_stylesheet_directory_uri() . '/build/course-outline/index.js',
		array(),
		filemtime( get_stylesheet_directory() . '/build/course-outline/index.js' ),
		true
	);

	if ( is_singular( 'course' ) ) {
		$lesson_data = get_lesson_data();
		wp_localize_script(
			'wporg-learn-2024-course-outline',
			'wporgCourseOutlineData',
			$lesson_data
		);
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_assets' );

/**
 * Get the titles and status icons of specific lessons.
 *
 * The returned array $lesson_data has the following structure:
 * [
 *     'in-progress' => [
 *         [
 *             'title' => (string) The title of the lesson,
 *             'icon'  => (string) The icon HTML for in-progress status,
 *         ],
 *         ...
 *     ],
 *     'locked' => [
 *         [
 *             'title' => (string) The title of the lesson,
 *             'icon'  => (string) The icon HTML for locked status,
 *         ],
 *         ...
 *     ]
 * ]
 *
 * @return array $lesson_data Array of lesson data.
 */
function get_lesson_data() {
	$lesson_data = array();
	$lesson_ids = Sensei()->course->course_lessons( get_the_ID(), 'publish', 'ids' );

	foreach ( $lesson_ids as $lesson_id ) {
		$user_lesson_status = Sensei_Utils::user_lesson_status( $lesson_id, get_current_user_id() );
		$lesson_title = get_the_title( $lesson_id );
		$is_preview_lesson = Sensei_Utils::is_preview_lesson( $lesson_id );

		// Add in-progress lesson title and icon to lesson data
		if ( $user_lesson_status ) {
			$lesson_status = $user_lesson_status->comment_approved;
			if ( 'in-progress' === $lesson_status ) {
				$icon = Sensei()->assets->get_icon( 'half-filled-circle', 'wp-block-sensei-lms-course-outline-lesson__status--in-progress' );
				$lesson_data['in-progress'][] = array(
					'title' => $lesson_title,
					'icon' => $icon,
				);
			}
		}

		// Add previewable and prerequisite-required lesson title and icon to lesson data
		if ( ( ! $is_preview_lesson && ! Sensei_Course::is_user_enrolled( get_the_ID() ) )
			|| ! Sensei_Lesson::is_prerequisite_complete( $lesson_id, get_current_user_id() )
		) {
			$icon = Sensei()->assets->get_icon( 'lock', 'wp-block-sensei-lms-course-outline-lesson__status--locked' );
			$lesson_data['locked'][] = array(
				'title' => $lesson_title,
				'icon' => $icon,
			);
		}
	}

	return $lesson_data;
}
