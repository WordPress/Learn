<?php
/**
 * Checklist lesson pattern template file.
 *
 * @package  sensei-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- wp:paragraph {"placeholder":"<?php esc_html_e( 'Use the Tasklist block as a way of keeping track of assignments and activities in a lesson.', 'sensei-pro' ); ?>"} -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:sensei-pro/task-list -->
<ul class="sensei-pro-task-list wp-block-sensei-pro-task-list" as="ul" data-sensei-wp-block="{}"><!--sensei:inner-blocks-->
	<!-- wp:sensei-pro/task-list-task -->
	<li class="sensei-pro-task-list__task wp-block-sensei-pro-task-list-task" as="li" data-sensei-wp-block="{&quot;checked&quot;:false,&quot;text&quot;:&quot;<?php esc_html_e( 'Task 1', 'sensei-pro' ); ?>&quot;}"><label class="sensei-pro-task-list__task-checkbox"><input type="checkbox"/><a role="button" tabindex="-1"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 18.6L3.5 13l1-1L9 16.4l9.5-9.9 1 1z" fill="currentColor"></path></svg></a></label><!--sensei:inner-blocks-->
		<p class="sensei-pro-task-list__task-text"><?php esc_html_e( 'Task 1', 'sensei-pro' ); ?></p>
		<!--/sensei:inner-blocks--></li>
	<!-- /wp:sensei-pro/task-list-task -->

	<!-- wp:sensei-pro/task-list-task -->
	<li class="sensei-pro-task-list__task wp-block-sensei-pro-task-list-task" as="li" data-sensei-wp-block="{&quot;checked&quot;:false,&quot;text&quot;:&quot;<?php esc_html_e( 'Task 2', 'sensei-pro' ); ?>&quot;}"><label class="sensei-pro-task-list__task-checkbox"><input type="checkbox"/><a role="button" tabindex="-1"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 18.6L3.5 13l1-1L9 16.4l9.5-9.9 1 1z" fill="currentColor"></path></svg></a></label><!--sensei:inner-blocks-->
		<p class="sensei-pro-task-list__task-text"><?php esc_html_e( 'Task 2', 'sensei-pro' ); ?></p>
		<!--/sensei:inner-blocks--></li>
	<!-- /wp:sensei-pro/task-list-task -->

	<!-- wp:sensei-pro/task-list-task -->
	<li class="sensei-pro-task-list__task wp-block-sensei-pro-task-list-task" as="li" data-sensei-wp-block="{&quot;checked&quot;:false,&quot;text&quot;:&quot;<?php esc_html_e( 'Task 3', 'sensei-pro' ); ?>&quot;}"><label class="sensei-pro-task-list__task-checkbox"><input type="checkbox"/><a role="button" tabindex="-1"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9 18.6L3.5 13l1-1L9 16.4l9.5-9.9 1 1z" fill="currentColor"></path></svg></a></label><!--sensei:inner-blocks-->
		<p class="sensei-pro-task-list__task-text"><?php esc_html_e( 'Task 3', 'sensei-pro' ); ?></p>
		<!--/sensei:inner-blocks--></li>
	<!-- /wp:sensei-pro/task-list-task -->

	<!-- wp:sensei-pro/task-list-task /-->
	<!--/sensei:inner-blocks--></ul>
<!-- /wp:sensei-pro/task-list -->

<!-- wp:sensei-lms/lesson-actions -->
<div class="wp-block-sensei-lms-lesson-actions">
	<div class="sensei-buttons-container">
		<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper">
			<div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'Take Quiz', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-view-quiz -->

		<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-complete-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-complete-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-complete-lesson -->

		<!-- wp:sensei-lms/button-next-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-next-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-next-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-next-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'Next Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-next-lesson -->

		<!-- wp:sensei-lms/button-reset-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-reset-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-reset-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-reset-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Reset Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-reset-lesson -->
	</div>
</div>
<!-- /wp:sensei-lms/lesson-actions -->
