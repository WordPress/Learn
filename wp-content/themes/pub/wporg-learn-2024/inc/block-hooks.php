<?php
/**
 * Blocks hooks.
 *
 * @package wporg-learn-2024
 */

namespace WordPressdotorg\Theme\Learn_2024\Block_Hooks;

use WP_HTML_Tag_Processor, Sensei_Utils, Sensei_Course, Sensei_Lesson;

add_filter( 'render_block_data', __NAMESPACE__ . '\modify_header_template_part' );
add_filter( 'render_block_data', __NAMESPACE__ . '\modify_course_outline_lesson_block_attrs' );
add_filter( 'render_block_sensei-lms/course-outline', __NAMESPACE__ . '\update_course_outline_block_add_aria' );
add_filter( 'render_block_sensei-lms/course-theme-notices', __NAMESPACE__ . '\update_lesson_quiz_notice_text' );
add_filter( 'render_block_sensei-lms/quiz-actions', __NAMESPACE__ . '\update_quiz_actions' );

/**
 * Update header template based on current query.
 * Since the search results for courses and lessons still use their respective archive templates,
 * we need to update the header template part to display the correct title.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array The updated block.
 */
function modify_header_template_part( $parsed_block ) {
	if (
		'core/template-part' === $parsed_block['blockName'] &&
		! empty( $parsed_block['attrs']['slug'] ) &&
		str_starts_with( $parsed_block['attrs']['slug'], 'header' ) &&
		is_search()
	) {
		$parsed_block['attrs']['slug'] = 'header-second-archive-title';
	}
	return $parsed_block;
}

/**
 * Add the status to the outline lesson block as a class, so that it can be
 * read by the `update_course_outline_block_add_aria` function.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array The updated block.
 */
function modify_course_outline_lesson_block_attrs( $parsed_block ) {
	if (
		'sensei-lms/course-outline-lesson' !== $parsed_block['blockName'] ||
		! isset( $parsed_block['attrs']['id'] )
	) {
		return $parsed_block;
	}

	$lesson_id = $parsed_block['attrs']['id'];
	$classes = array();
	$classes[] = $parsed_block['attrs']['className'] ?? '';

	$status = 'not-started';
	$lesson_status = Sensei_Utils::user_lesson_status( $lesson_id );
	if ( $lesson_status ) {
		$status = $lesson_status->comment_approved;
	}
	$classes[] = 'is-' . $status;

	// Add previewable and prerequisite-required lesson title to lesson data
	if (
		( ! Sensei_Utils::is_preview_lesson( $lesson_id ) && ! Sensei_Course::is_user_enrolled( get_the_ID() ) )
		|| ! Sensei_Lesson::is_prerequisite_complete( $lesson_id, get_current_user_id() )
	) {
		$classes[] = 'is-locked';
	}

	$parsed_block['attrs']['className'] = implode( ' ', $classes );

	return $parsed_block;
}

/**
 * Filter the course outline block to add accessible attributes.
 *
 * Note, this filters the entire `sensei-lms/course-outline` block instead of
 * `sensei-lms/course-outline-lesson` due to Sensei's rendering of these
 * blocks. The outline module & outline lesson blocks are not rendered
 * individually, so they cannot be independently filtered.
 *
 * @param string $block_content The block content.
 *
 * @return string The updated icon HTML with aria data.
 */
function update_course_outline_block_add_aria( $block_content ) {
	$html = new WP_HTML_Tag_Processor( $block_content );

	$label = '';
	while ( $html->next_tag( array( 'class_name' => 'wp-block-sensei-lms-course-outline-lesson' ) ) ) {
		if ( $html->has_class( 'is-complete' ) || $html->has_class( 'is-passed' ) ) {
			$label = __( 'Completed', 'wporg-learn' );
		} else if ( $html->has_class( 'is-in-progress' ) ) {
			$label = __( 'In progress', 'wporg-learn' );
		} else {
			$label = __( 'Not started', 'wporg-learn' );
		}

		$html->next_tag( 'svg' );
		$html->set_attribute( 'aria-label', $label );
		$html->set_attribute( 'role', 'img' );
	}
	return $html->get_updated_html();
}

/**
 * Replace the text for the lesson quiz notice.
 *
 * @param string $block_content The block content.
 *
 * @return string
 */
function update_lesson_quiz_notice_text( $block_content ) {
	if ( is_singular( 'lesson' ) && is_quiz_ungraded() ) {
		// Remove the text "Awaiting grade" in the quiz notice.
		$block_content = str_replace(
			'<div class="sensei-course-theme-lesson-quiz-notice__text">Awaiting grade</div>',
			'',
			$block_content
		);

		// Add a new paragraph between the notice content and actions.
		$new_p_tag = sprintf(
			'<p class="sensei-course-theme-lesson-quiz-notice__description">%s</p>',
			esc_html__( 'This is an ungraded quiz. Use it to check your comfort level with what you’ve learned.', 'wporg-learn' )
		);

		$block_content = str_replace(
			'<div class="sensei-course-theme-lesson-quiz-notice__actions">',
			$new_p_tag . '<div class="sensei-course-theme-lesson-quiz-notice__actions">',
			$block_content
		);
	}

	return $block_content;
}

/**
 * Customize the quiz actions.
 *
 * @param string $block_content The block content.
 *
 * @return string
 */
function update_quiz_actions( $block_content ) {
	if ( is_singular( 'quiz' ) && is_quiz_ungraded() ) {
		$lesson_id = Sensei()->quiz->get_lesson_id();
		$lesson_link = get_permalink( $lesson_id );

		// Add a new button to go back to the lesson.
		$new_button_block = do_blocks( '
			<!-- wp:button {"className":"has-text-align-center is-style-fill","fontSize":"normal","fontFamily":"inter"} -->
			<div class="wp-block-button has-custom-font-size has-text-align-center is-style-fill has-inter-font-family has-normal-font-size">
				<a class="wp-block-button__link wp-element-button" style="font-weight:600;line-height:1;outline:unset" href="' . esc_attr( $lesson_link ) . '">' . esc_html__( 'Back to lesson', 'wporg-learn' ) . '</a>
			</div>
			<!-- /wp:button -->
		');

		$block_content = str_replace(
			'<div class="sensei-quiz-actions-secondary">',
			$new_button_block . '<div class="sensei-quiz-actions-secondary">',
			$block_content
		);
	}

	return $block_content;
}

/**
 * Check if the quiz is ungraded.
 *
 * @return bool
 */
function is_quiz_ungraded() {
	$lesson_id = Sensei_Utils::get_current_lesson();
	$quiz_id   = Sensei()->lesson->lesson_quizzes( $lesson_id );
	$user_id   = get_current_user_id();
	$quiz_progress = Sensei()->quiz_progress_repository->get( $quiz_id, $user_id );

	if ( 'ungraded' === $quiz_progress->get_status() ) {
		return true;
	}

	return false;
}
