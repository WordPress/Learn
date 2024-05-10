<?php
/**
 * Title: Sensei Lesson content
 * Slug: wporg-learn-2024/sensei-lesson
 * Inserter: no
 *
 * Copied from https://github.com/Automattic/sensei/blob/trunk/themes/sensei-course-theme/templates/default/lesson.php
 */

?>

<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-header"} /-->

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__columns","className":"sensei-version\u002d\u002d4-16-2"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__columns sensei-version--4-16-2">

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__sidebar","className":"","style"={"spacing":{"margin":{"top":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|20","right":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|edge-space"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-course-theme__sidebar" style="margin-top:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:sensei-lms/course-navigation /-->

	</div>
	<!-- /wp:sensei-lms/ui -->

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__main-content","lock":{"move":false,"remove":false},"style"={"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|edge-space"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__main-content" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--edge-space)">

		<!-- wp:sensei-lms/course-theme-lesson-module /-->

		<!-- wp:post-title {"level":1,"fontSize":"heading-1"} /-->

		<!-- wp:sensei-lms/course-theme-notices /-->

		<!-- wp:post-content {"layout":{"inherit":true}} /-->

		<!-- wp:group {"style":{"spacing":{"margin":{"top":"40px"}}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group" style="margin-top:40px">
			<!-- wp:sensei-lms/page-actions {"style":{"spacing":{"blockGap":"43px"}}} /-->

			<!-- wp:group {"style":{"spacing":{"margin":{"top":"20px"}}},"className":"sensei-lesson-footer","layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group sensei-lesson-footer" style="margin-top:20px">
				<!-- wp:sensei-lms/lesson-actions {"toggledBlocks":{"sensei-lms/button-reset-lesson":false}} -->
				<div class="wp-block-sensei-lms-lesson-actions">
					<div class="sensei-buttons-container">
						<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true,"className":"is-style-outline"} -->
						<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper"><div class="wp-block-sensei-lms-button-complete-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete Lesson', 'sensei-lms' ); ?></button></div></div>
						<!-- /wp:sensei-lms/button-complete-lesson -->

						<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
						<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper"><div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Take Quiz', 'sensei-lms' ); ?></button></div></div>
						<!-- /wp:sensei-lms/button-view-quiz -->

						<!-- wp:sensei-lms/button-lesson-completed {"inContainer":true,"className":"is-style-outline"} -->
						<div class="wp-block-sensei-lms-button-lesson-completed is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-lesson-completed__wrapper"><div class="wp-block-sensei-lms-button-lesson-completed is-style-outline wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Completed', 'sensei-lms' ); ?></button></div></div>
						<!-- /wp:sensei-lms/button-lesson-completed -->

						<!-- wp:sensei-lms/button-next-lesson {"inContainer":true} -->
						<div class="wp-block-sensei-lms-button-next-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-next-lesson__wrapper"><div class="wp-block-sensei-lms-button-next-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Next Lesson', 'sensei-lms' ); ?></button></div></div>
						<!-- /wp:sensei-lms/button-next-lesson -->
					</div>
				</div>
				<!-- /wp:sensei-lms/lesson-actions -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:sensei-lms/ui -->
</div>
<!-- /wp:sensei-lms/ui -->
