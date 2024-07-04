<?php

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	if ( ! is_singular( 'course' ) ) {
		return;
	}

	$script_asset_path = get_stylesheet_directory() . '/build/course-outline/index.asset.php';
	if ( ! file_exists( $script_asset_path ) ) {
		wp_die( 'You need to run `yarn start` or `yarn build` to build the required assets.' );
	}

	$script_asset = require( $script_asset_path );
	wp_enqueue_script(
		'wporg-learn-2024-course-outline',
		get_stylesheet_directory_uri() . '/build/course-outline/index.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	$lesson_data = get_lesson_data();
	wp_localize_script(
		'wporg-learn-2024-course-outline',
		'wporgCourseOutlineData',
		$lesson_data
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_assets' );

/**
 * Get the titles of specific status lessons.
 *
 * The returned array $lesson_data has the following structure:
 * [
 *     'in-progress' => [ (string) The title of the lesson, ... ],
 *     'locked' => [ (string) The title of the lesson, ... ],
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

		// Add in-progress lesson title to lesson data
		if ( $user_lesson_status ) {
			$lesson_status = $user_lesson_status->comment_approved;
			if ( 'in-progress' === $lesson_status ) {
				$lesson_data['in-progress'][] = $lesson_title;
			}
		}

		// Add previewable and prerequisite-required lesson title to lesson data
		if ( ( ! $is_preview_lesson && ! Sensei_Course::is_user_enrolled( get_the_ID() ) )
			|| ! Sensei_Lesson::is_prerequisite_complete( $lesson_id, get_current_user_id() )
		) {
			$lesson_data['locked'][] = $lesson_title;
		}
	}

	return $lesson_data;
}
