<?php
/**
 * Title: Sensei Quiz Notices
 * Slug: wporg-learn-2024/sensei-quiz-notices
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

$lesson_id         = Sensei()->quiz->get_lesson_id();
$course_id         = Sensei()->lesson->get_course_id( $lesson_id );
$is_learning_mode  = Sensei_Course_Theme_Option::has_learning_mode_enabled( $course_id );
$is_awaiting_grade = Sensei_Quiz::is_quiz_awaiting_grade_for_user( $lesson_id, get_current_user_id() );
$is_pending_grade = $is_learning_mode && $is_awaiting_grade;
$is_from_lesson = strpos( $_SERVER['HTTP_REFERER'], '/lesson/' ) !== false;

?>

<?php if ( $is_pending_grade && $is_from_lesson ) : ?>
	<!-- wp:wporg/notice {"type":"info","style":{"spacing":{"margin":{"top":0,"bottom":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-wporg-notice is-info-notice" style="margin-top:-10px;margin-bottom:var(--wp--preset--spacing--40)">
		<div class="wp-block-wporg-notice__icon"></div>
		<div class="wp-block-wporg-notice__content">
			<p><?php esc_html_e( '[TBD. User is waiting for a grade]', 'wporg-learn' ); ?></p>
		</div>
	</div>
	<!-- /wp:wporg/notice -->
<?php elseif ( $is_pending_grade ) : ?>
	<!-- wp:wporg/notice {"style":{"spacing":{"margin":{"top":0,"bottom":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-wporg-notice is-tip-notice" style="margin-top:-10px;margin-bottom:var(--wp--preset--spacing--40)">
		<div class="wp-block-wporg-notice__icon"></div>
		<div class="wp-block-wporg-notice__content">
			<p><?php esc_html_e( 'Your answers have been submitted and the quiz will be graded soon.', 'wporg-learn' ); ?></p>
		</div>
	</div>
	<!-- /wp:wporg/notice -->
<?php else : ?>	
	<!-- wp:sensei-lms/course-theme-notices /-->
<?php endif; ?>
