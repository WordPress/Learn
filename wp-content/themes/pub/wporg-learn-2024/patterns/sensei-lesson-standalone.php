<?php
/**
 * Title: Standalone Sensei Lesson
 * Slug: wporg-learn-2024/sensei-lesson-standalone
 * Inserter: no
 */

?>

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__header","className":"sensei-version\u002d\u002d4-16-2 sensei-course-theme__header--standalone"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-version--4-16-2 sensei-course-theme__header sensei-course-theme__header--standalone">
	
	<!-- wp:group {"style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","top":"0px","bottom":"0px"}}},"backgroundColor":"white","className":"sensei-course-theme-header-content","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group sensei-course-theme-header-content has-white-background-color has-background" style="padding-top:0px;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:0px;padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:wporg/site-breadcrumbs {"fontSize":"small","style":{"spacing":{"padding":{"top":"18px","bottom":"18px"}}}} /-->

		<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">

			<!-- wp:paragraph {"className":"is-style-short-text","fontSize":"small"} -->
			<p class="is-style-short-text has-small-font-size"><a href="<?php echo esc_url( get_post_type_archive_link( 'lesson' ) ); ?>"><?php echo esc_html_e( 'Exit lesson', 'wporg-learn' ); ?></a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:sensei-lms/ui -->

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__columns","className":"sensei-version\u002d\u002d4-16-2 sensei-course-theme__columns--standalone"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__columns sensei-course-theme__columns--standalone sensei-version--4-16-2">

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__main-content","lock":{"move":false,"remove":false},"style"={"spacing":{"margin":{"top":"var:preset|spacing|30"},"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__main-content" style="margin-top:var(--wp--preset--spacing--30);padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">

		<!-- wp:post-title {"level":1,"fontSize":"heading-1"} /-->

		<!-- wp:post-content {"layout":{"inherit":true}} /-->

		<!-- wp:group {"style":{"spacing":{"margin":{"top":"40px"}}},"layout":{"type":"constrained"},"className":"sensei-lesson-footer"} -->
		<div class="wp-block-group sensei-lesson-footer" style="margin-top:40px">
			<!-- wp:sensei-lms/page-actions {"style":{"spacing":{"blockGap":"43px"}}} /-->

			<!-- wp:group {"style":{"spacing":{"margin":{"top":"20px"}}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group" style="margin-top:20px">

				<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-actions"} /-->

			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:sensei-lms/ui -->
</div>
<!-- /wp:sensei-lms/ui -->

<!-- wp:group {"align":"full","style":{"border":{"top":{"color":"var:preset|color|light-grey-1","width":"1px"},"right":{},"bottom":{},"left":{}},"spacing":{"margin":{"top":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="margin-top:var(--wp--preset--spacing--20);border-top-color:var(--wp--preset--color--light-grey-1);border-top-width:1px">
	
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:pattern {"slug":"wporg-learn-2024/content-feedback"} /-->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
