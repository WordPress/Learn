<?php
/**
 * Title: Front Page Content
 * Slug: wporg-learn-2024/front-page-content
 * Inserter: no
 */

?>

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Get Started', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
<p class="has-charcoal-4-color has-text-color has-link-color" style="margin-top:0"><?php esc_html_e( 'Learning Pathways help you build your skills progressively so you can go from beginner to advanced at your own pace.', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:wporg-learn/learning-pathway-cards {"isMini":true,"style":{"spacing":{"margin":{"top":"40px"}}}} /-->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Featured Courses', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Focus on building your skills through a series of lessons in various formats.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/courses/' ) ); ?>"><?php esc_html_e( 'See all Courses', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"perPage":6,"postType":"course","courseFeatured":true},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-course-h3","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-courses"} /-->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Featured Lessons', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Improve your WordPress expertise with versatile lessons featuring a blend of videos, practical exercises, quizzes, and text-based content.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/lessons/' ) ); ?>"><?php esc_html_e( 'See all Lessons', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":6,"postType":"lesson","lessonFeatured":true},"namespace":"wporg-learn/lesson-grid","align":"wide","className":"wporg-learn-lesson-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-lesson-grid wporg-learn-card-grid">
	
	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-lesson-h3","className":"has-display-contents"} /-->
		
	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-lessons"} /-->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Upcoming Online Workshops', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Join a live session alongside other learners, led by experienced WordPress professionals.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/online-workshops' ) ); ?>"><?php esc_html_e( 'See all Online Workshops', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:wporg-learn/upcoming-online-workshops {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50"}}}} /-->

<!-- wp:group {"layout":{"type":"constrained","justifyContent":"left","contentSize":"750px"}} -->
<div class="wp-block-group">
	
	<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"bottom":"var:preset|spacing|20"}}},"fontSize":"heading-1","fontFamily":"eb-garamond"} -->
	<h2 class="wp-block-heading has-eb-garamond-font-family has-heading-1-font-size" style="margin-bottom:var(--wp--preset--spacing--20);font-style:normal;font-weight:400"><?php esc_html_e( 'Share your WordPress expertise', 'wporg-learn' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
	<p style="margin-bottom:var(--wp--preset--spacing--40)"><?php esc_html_e( 'Behind every course, lesson, and live workshop is a passionate collective of professionals working to offer the highest-quality educational content. If you love WordPress, have knowledge to share, and want to contribute to a thriving open source community—get involved with the Training team.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contribute"><?php esc_html_e( 'Get involved', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://make.wordpress.org/training/handbook/"><?php esc_html_e( 'Read the Training team handbook', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->

	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
