<?php
/**
 * Title: Courses Archive Content
 * Slug: wporg-learn-2024/my-courses-content
 * Inserter: no
 */

?>

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|20"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"730px"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--20)">

	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading"><?php esc_html_e( 'My courses', 'wporg-learn' ); ?></h1>
	<!-- /wp:heading -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right"},"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group alignwide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
	<div class="wp-block-group wporg-query-filters">
		<!-- wp:wporg/query-filter {"key":"student-course","multiple":false} /-->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"postType":"course","perPage":12,"offset":0,"inherit":false,"sticky":"","pages":0,"order":"desc","orderBy":"date","author":"","search":"","exclude":[]},"className":"wp-block-sensei-lms-course-list wp-block-sensei-lms-course-list\u002d\u002dis-list-view wporg-learn-course-grid"} -->
<div class="wp-block-query wp-block-sensei-lms-course-list wp-block-sensei-lms-course-list--is-list-view wporg-learn-course-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

			<!-- wp:post-featured-image {"isLink":true,"height":"","align":"center"} /-->

			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

				<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":1.6},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

				<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

				<!-- wp:sensei-lms/course-progress {"barColor":"blueberry-1","barBackgroundColor":"blueberry-3","height":8,"className":"wporg-learn-sidebar-course-progress"} /-->

				<!-- wp:sensei-lms/course-actions -->

					<!-- wp:sensei-lms/button-take-course {"align":"right"} -->
					<div class="wp-block-sensei-lms-button-take-course is-style-default wp-block-sensei-button wp-block-button has-text-align-right"><button class="wp-block-button__link"><?php esc_html_e( 'Start Course', 'wporg-learn' ); ?></button></div>
					<!-- /wp:sensei-lms/button-take-course -->

					<!-- wp:sensei-lms/button-continue-course {"align":"right"} -->
					<div class="wp-block-sensei-lms-button-continue-course is-style-default wp-block-sensei-button wp-block-button has-text-align-right"><a class="wp-block-button__link"><?php esc_html_e( 'Continue', 'wporg-learn' ); ?></a></div>
					<!-- /wp:sensei-lms/button-continue-course -->

					<!-- wp:sensei-lms/button-view-results {"align":"right","className":"is-style-default"} -->
					<div class="wp-block-sensei-lms-button-view-results is-style-default wp-block-sensei-button wp-block-button has-text-align-right"><a class="wp-block-button__link"><?php esc_html_e( 'Visit Results', 'wporg-learn' ); ?></a></div>
					<!-- /wp:sensei-lms/button-view-results -->

				<!-- /wp:sensei-lms/course-actions -->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
		<p><?php esc_html_e( 'No Courses found.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->

		<!-- wp:query-pagination-previous {"label":"Previous"} /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next {"label":"Next"} /-->

	<!-- /wp:query-pagination -->

</div>
<!-- /wp:query -->
