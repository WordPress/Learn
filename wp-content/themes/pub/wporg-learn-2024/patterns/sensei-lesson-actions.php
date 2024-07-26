<?php
/**
 * Title: Sensei Lesson Actions
 * Slug: wporg-learn-2024/sensei-lesson-actions
 * Inserter: no
 *
 * Original source: https://github.com/Automattic/sensei/blob/af62fb1115daf2063bc56331a7d8b1b3ea805866/themes/sensei-course-theme/templates/default/lesson.php
 */

?>

<!-- wp:sensei-lms/lesson-actions {"toggledBlocks":{"sensei-lms/button-reset-lesson":false}} -->
<div class="wp-block-sensei-lms-lesson-actions">
	<div class="sensei-buttons-container">
		<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true,"className":"is-style-outline"} -->
		<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete lesson', 'sensei-lms' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-complete-lesson -->

		<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper">
			<div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'Take quiz', 'sensei-lms' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-view-quiz -->

		<!-- wp:sensei-lms/button-lesson-completed {"inContainer":true,"className":"is-style-outline"} -->
		<div class="wp-block-sensei-lms-button-lesson-completed is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-lesson-completed__wrapper">
			<div class="wp-block-sensei-lms-button-lesson-completed is-style-outline wp-block-sensei-button wp-block-button has-text-align-left">
				<div class="wp-block-button__link"><?php esc_html_e( 'Completed', 'sensei-lms' ); ?></div>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-lesson-completed -->

		<!-- wp:sensei-lms/button-next-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-next-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-next-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-next-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<div class="wp-block-button__link"><?php esc_html_e( 'Next lesson', 'sensei-lms' ); ?></div>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-next-lesson -->
	</div>
</div>
<!-- /wp:sensei-lms/lesson-actions -->
