<?php
/**
 * Title: Sensei Lesson columns
 * Slug: wporg-learn-2024/sensei-lesson-columns
 * Inserter: no
 */

$lesson_id = Sensei_Utils::get_current_lesson();
$module = Sensei()->modules->get_lesson_module( $lesson_id );
$is_completed = Sensei_Utils::user_completed_lesson( $lesson_id );

?>

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__columns","className":"sensei-version\u002d\u002d4-16-2"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__columns sensei-version--4-16-2">

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__sidebar","style"={"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|30","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-course-theme__sidebar" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

		<!-- wp:sensei-lms/course-navigation /-->

	</div>
	<!-- /wp:sensei-lms/ui -->

	<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__main-content","lock":{"move":false,"remove":false},"style"={"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|edge-space"}}}} -->
	<div class="wp-block-sensei-lms-ui sensei-course-theme__main-content" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--edge-space)">

		<!-- wp:sensei-lms/course-theme-lesson-module /-->

		<?php if ( $is_completed ) : ?>
			<!-- wp:wporg/notice {"style":{"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} -->
			<div class="wp-block-wporg-notice is-success-notice" 
				style="<?php echo $module ? 'margin-top:var(--wp--preset--spacing--20)' : 'margin-bottom:var(--wp--preset--spacing--50)'; ?>">
				<div class="wp-block-wporg-notice__icon"></div>
				<div class="wp-block-wporg-notice__content">
					<p><?php esc_html_e( 'You already completed this lesson', 'wporg-learn' ); ?></p>
				</div>
			</div>
			<!-- /wp:wporg/notice -->
		<?php endif; ?>

		<?php if ( $module ) : ?>
			<!-- wp:post-title {"level":1,"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}},"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"heading-3","fontFamily":"inter"} /-->
		<?php else : ?>
			<!-- wp:post-title {"level":1,"fontSize":"heading-3","fontFamily":"inter","style":{"spacing":{"margin":{"top":"0"}},"typography":{"lineHeight":"1","fontStyle":"normal","fontWeight":"600"}}} /-->	 
		<?php endif; ?> 

		<!-- wp:post-content {"layout":{"type":"constrained","justifyContent":"left"}} /-->

		<!-- wp:sensei-lms/course-theme-notices /-->

		<?php if ( is_user_logged_in() ) : ?>
			<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var(--wp--preset--spacing--50)"}}},"layout":{"type":"constrained"},"className":"sensei-lesson-footer"} -->
			<div class="wp-block-group sensei-lesson-footer" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--50)">
				<!-- wp:sensei-lms/page-actions {"style":{"spacing":{"blockGap":"43px"}}} /-->

				<!-- wp:group {"style":{"spacing":{"margin":{"top":"0"}}}} -->
				<div class="wp-block-group" style="margin-top:0">

					<!-- wp:pattern {"slug":"wporg-learn-2024/sensei-lesson-actions"} /-->

				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		<?php endif; ?>

		<!-- wp:group {"align":"full","style":{"border":{"top":{"color":"var:preset|color|light-grey-1","width":"1px"},"right":{},"bottom":{},"left":{}},"spacing":{"margin":{"top":"0"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group alignfull" style="margin-top:0;border-top-color:var(--wp--preset--color--light-grey-1);border-top-width:1px">

			<!-- wp:group {"layout":{"type":"constrained"},"spacing":{"margin":{"top":"var(--wp--preset--spacing--30)"}}} -->
			<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--30)">

				<!-- wp:pattern {"slug":"wporg-learn-2024/content-feedback"} /-->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:sensei-lms/ui -->
</div>
<!-- /wp:sensei-lms/ui -->
