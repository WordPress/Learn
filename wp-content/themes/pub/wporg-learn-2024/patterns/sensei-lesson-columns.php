<?php
/**
 * Title: Sensei Lesson columns
 * Slug: wporg-learn-2024/sensei-lesson-columns
 * Inserter: no
 */

?>

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__columns","className":"sensei-version\u002d\u002d4-16-2"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__columns sensei-version--4-16-2">

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__sidebar","className":"","style"={"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|edge-space"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-course-theme__sidebar" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:sensei-lms/course-navigation /-->

		<!-- wp:group {"style":{"border":{"top":{"color":"var:preset|color|light-grey-1","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group" style="border-top-color:var(--wp--preset--color--light-grey-1);border-top-width:1px;padding-top:var(--wp--preset--spacing--20)">

			<!-- wp:pattern {"slug":"wporg-learn-2024/content-feedback"} /-->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:sensei-lms/ui -->

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__main-content","lock":{"move":false,"remove":false},"style"={"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|edge-space"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__main-content" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--edge-space)">

		<!-- wp:sensei-lms/course-theme-lesson-module /-->

		<!-- wp:post-title {"level":1,"fontSize":"heading-1"} /-->

		<!-- wp:sensei-lms/course-theme-notices /-->

		<!-- wp:post-content {"layout":{"inherit":true}} /-->

		<?php if ( is_user_logged_in() ) : ?>
			<!-- wp:group {"style":{"spacing":{"margin":{"top":"40px"}}},"layout":{"type":"constrained"},"className":"sensei-lesson-footer"} -->
			<div class="wp-block-group sensei-lesson-footer" style="margin-top:40px">
				<!-- wp:sensei-lms/page-actions {"style":{"spacing":{"blockGap":"43px"}}} /-->

				<!-- wp:group {"style":{"spacing":{"margin":{"top":"20px"}}}} -->
				<div class="wp-block-group" style="margin-top:20px">

					<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-actions"} /-->

				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		<?php endif; ?>
	</div>
	<!-- /wp:sensei-lms/ui -->
</div>
<!-- /wp:sensei-lms/ui -->
