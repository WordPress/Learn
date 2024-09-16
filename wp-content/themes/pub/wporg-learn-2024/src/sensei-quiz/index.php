<?php
/**
 * Add elements or adjust the layout for the sensei quiz-related pages.
 */

namespace WordPressdotorg\Theme\Learn_2024\SENSEI_QUIZ;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'render_block_sensei-lms/course-theme-notices', __NAMESPACE__ . '\customize_lesson_quiz_notice_text' );
add_filter( 'render_block_sensei-lms/quiz-actions', __NAMESPACE__ . '\customize_quiz_actions' );

/**
 * Replace the text for the lesson quiz notice.
 *
 * @param string $block_content The block content.
 *
 * @return string
 */
function customize_lesson_quiz_notice_text( $block_content ) {
	if ( is_singular( 'lesson' ) && is_quiz_ungraded() ) {
		$tag_processor = new \WP_HTML_Tag_Processor( $block_content );

		// Hide the text "Awaiting grade" in the quiz notice.
		if ( $tag_processor->next_tag( array(
			'tag_name' => 'div',
			'class_name' => 'sensei-course-theme-lesson-quiz-notice__text',
		) ) ) {
			$tag_processor->set_attribute( 'style', 'display:none;' );
		}

		// Add a new paragraph between the notice content and actions.
		$new_p_tag = sprintf(
			'<p class="sensei-course-theme-lesson-quiz-notice__description">%s</p>',
			esc_html__( '[TBD. Sentence conveying that user is waiting for the teacher to assign a grade]', 'wporg-learn' )
		);

		$updated_html = str_replace(
			'<div class="sensei-course-theme-lesson-quiz-notice__actions">',
			$new_p_tag . '<div class="sensei-course-theme-lesson-quiz-notice__actions">',
			$tag_processor->get_updated_html()
		);

		return $updated_html;
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
function customize_quiz_actions( $block_content ) {
	if ( is_singular( 'quiz' ) && is_quiz_ungraded() ) {
		$tag_processor = new \WP_HTML_Tag_Processor( $block_content );
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

		$updated_html = str_replace(
			'<div class="sensei-quiz-actions-secondary">',
			$new_button_block . '<div class="sensei-quiz-actions-secondary">',
			$tag_processor->get_updated_html()
		);

		return $updated_html;
	}

	return $block_content;
}

/**
 * Check if the quiz is ungraded.
 *
 * @return bool
 */
function is_quiz_ungraded() {
	$lesson_id = \Sensei_Utils::get_current_lesson();
	$quiz_id   = Sensei()->lesson->lesson_quizzes( $lesson_id );
	$user_id   = get_current_user_id();
	$quiz_progress = Sensei()->quiz_progress_repository->get( $quiz_id, $user_id );

	if ( 'ungraded' === $quiz_progress->get_status() ) {
		return true;
	}

	return false;
}
