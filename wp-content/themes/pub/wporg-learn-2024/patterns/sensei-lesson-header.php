<?php
/**
 * Title: Sensei Lesson header
 * Slug: wporg-learn-2024/sensei-lesson-header
 * Inserter: no
 *
 * Original source: https://github.com/Automattic/sensei/blob/af62fb1115daf2063bc56331a7d8b1b3ea805866/themes/sensei-course-theme/patterns/header.html
 */

?>

<!-- wp:sensei-lms/ui {"elementClass":"sensei-course-theme__header","className":"sensei-version\u002d\u002d4-16-2"} -->
<div class="wp-block-sensei-lms-ui sensei-course-theme__frame sensei-version--4-16-2 sensei-course-theme__header">
	<!-- wp:group {"style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","top":"0px","bottom":"0px"}}},"className":"sensei-course-theme-header-content","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group sensei-course-theme-header-content" style="padding-top:0px;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:0px;padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:group {"className":"wporg-breadcrumbs","align":"full","style":{"spacing":{"padding":{"top":"23px","bottom":"23px","right":"var:preset|spacing|edge-space"}}},"backgroundColor":"white","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
		<div class="wporg-breadcrumbs wp-block-group alignfull has-white-background-color has-background" style="padding-top:23px;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:23px;">
			<!-- wp:wporg/site-breadcrumbs {"fontSize":"small"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
		<div class="wp-block-group">

			<!-- wp:group {"style":{"spacing":{"blockGap":"16px"}},"className":"sensei-course-theme__header__info","layout":{"type":"flex","flexWrap":"nowrap"}} -->
			<div class="wp-block-group sensei-course-theme__header__info">
				<!-- wp:sensei-lms/course-theme-course-progress-counter {"fontSize":"small"} /-->

				<!-- wp:sensei-lms/exit-course {"fontSize":"small"} /-->
			</div>
			<!-- /wp:group -->

			<!-- wp:sensei-lms/sidebar-toggle-button /-->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

	<!-- wp:sensei-lms/course-theme-course-progress-bar /-->
</div>
<!-- /wp:sensei-lms/ui -->
