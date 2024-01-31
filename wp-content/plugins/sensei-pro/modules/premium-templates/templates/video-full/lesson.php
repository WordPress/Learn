<?php
/**
 * Full video template for displaying lesson in learning mode.
 *
 * @author      Automattic
 * @package     sensei-pro
 * @category    Templates
 * @version     1.20.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__header","className":"sensei-version--4-8-0"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-version--4-8-0 sensei-course-theme__header">
	<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"0px","bottom":"0px"},"padding":{"top":"6px","right":"24px","bottom":"6px","left":"24px"},"blockGap":"0px"}}} -->
	<div class="wp-block-group"
		style="margin-top:0px;margin-bottom:0px;padding-top:6px;padding-right:24px;padding-bottom:6px;padding-left:24px">
		<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"12px"}}} -->
		<div class="wp-block-group"><!-- wp:site-logo {"width":50,"className":"is-style-default"} /-->

			<!-- wp:sensei-lms/course-title /--></div>
		<!-- /wp:group -->

		<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"},"style":{"spacing":{"blockGap":"12px"}}} -->
		<div class="wp-block-group">
			<!-- wp:sensei-lms/course-theme-course-progress-counter /-->

			<!-- wp:sensei-lms/exit-course /-->

			<!-- wp:sensei-lms/sidebar-toggle-button /--></div>
		<!-- /wp:group --></div>
	<!-- /wp:group -->

	<!-- wp:sensei-lms/course-theme-course-progress-bar /--></div>
<!-- /wp:sensei-lms/ui -->

<!-- wp:sensei-lms/course-theme-lesson-video {"className":"alignfull","style":{"spacing":{"margin":{"bottom":"48px"}}}} /-->

<!-- wp:group {"className":"sensei-course-theme__main-content","style":{"spacing":{"blockGap":"2em","margin":{"top":"20px","bottom":"20px"}}},"layout":{"contentSize":"1300px","wideSize":"1300px","inherit":false}} -->
<div class="wp-block-group sensei-course-theme__main-content" style="margin-top:20px;margin-bottom:20px">
	<!-- wp:columns {"verticalAlignment":"top","style":{"spacing":{"blockGap":"100px"}}} -->
	<div class="wp-block-columns are-vertically-aligned-top">
		<!-- wp:column {"verticalAlignment":"top","width":"900px"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:900px">
			<!-- wp:post-title {"level":1} /-->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"sensei-course-theme__empty-column","verticalAlignment":"top","width":"320px"} -->
		<div class="wp-block-column is-vertically-aligned-top sensei-course-theme__empty-column" style="flex-basis:320px">
		</div><!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:columns {"verticalAlignment":"top","style":{"spacing":{"blockGap":"100px"}}} -->
	<div class="wp-block-columns are-vertically-aligned-top">
		<!-- wp:column {"verticalAlignment":"top","width":"900px"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:900px"><!-- wp:sensei-lms/course-theme-notices /-->

			<!-- wp:group {"layout":{"type":"default"},"style":{"spacing":{"margin":{"top":"0","bottom":"32px"}}}} -->
			<div class="wp-block-group" style="margin-top:0;margin-bottom:32px">

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
								<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper"><div class="wp-block-sensei-lms-button-complete-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete Lesson', 'sensei-pro' ); ?></button></div></div>
								<!-- /wp:sensei-lms/button-complete-lesson -->

								<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
								<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper"><div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Take Quiz', 'sensei-pro' ); ?></button></div></div>
								<!-- /wp:sensei-lms/button-view-quiz -->

								<!-- wp:sensei-lms/button-lesson-completed {"inContainer":true,"className":"is-style-outline"} -->
								<div class="wp-block-sensei-lms-button-lesson-completed is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-lesson-completed__wrapper"><div class="wp-block-sensei-lms-button-lesson-completed is-style-outline wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Completed', 'sensei-pro' ); ?></button></div></div>
								<!-- /wp:sensei-lms/button-lesson-completed -->

								<!-- wp:sensei-lms/button-next-lesson {"inContainer":true} -->
								<div class="wp-block-sensei-lms-button-next-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-next-lesson__wrapper"><div class="wp-block-sensei-lms-button-next-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left"><button class="wp-block-button__link"><?php esc_html_e( 'Next Lesson', 'sensei-pro' ); ?></button></div></div>
								<!-- /wp:sensei-lms/button-next-lesson -->
							</div>
						</div>
						<!-- /wp:sensei-lms/lesson-actions -->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:group --></div>
			<!-- /wp:group --></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"top","width":"320px"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:320px">
			<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__sidebar","className":"sensei-course-theme__sidebar\u002d\u002dis-sticky","style":{"spacing":{"padding":{"right":"20px","top":"20px","bottom":"20px","left":"20px"},"margin":{"top":"0","bottom":"32px"}}}} -->
			<div
				class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-course-theme__sidebar sensei-course-theme__sidebar--is-sticky"
				style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px;margin-top:0px;margin-bottom:32px">
				<!-- wp:sensei-lms/course-navigation /-->
				</div>
			<!-- /wp:sensei-lms/ui -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns --></div>
<!-- /wp:group -->
<!-- wp:sensei-lms/template-style {"lock":{"move":true,"remove":true}} -->
<style>
	.sensei-course-theme__sidebar:not(.has-background) {
		border: 1px solid currentColor;
	}
</style>
<!-- /wp:sensei-lms/template-style -->
