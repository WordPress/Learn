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

/**
 * Replace the text for the lesson quiz notice.
 *
 * @param string $block_content The block content.
 *
 * @return string
 */
function customize_lesson_quiz_notice_text( $block_content ) {
	$tag_processor = new \WP_HTML_Tag_Processor( $block_content );

	if ( $tag_processor->next_tag( array(
		'tag_name' => 'div',
		'class_name' => 'sensei-course-theme-lesson-quiz-notice',
	) ) ) {
		$lesson_id = \Sensei_Utils::get_current_lesson();
		$quiz_id   = Sensei()->lesson->lesson_quizzes( $lesson_id );
		$user_id   = get_current_user_id();
		$quiz_progress = Sensei()->quiz_progress_repository->get( $quiz_id, $user_id );

		if ( 'ungraded' === $quiz_progress->get_status() ) {
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
	}

	return $block_content;
}
