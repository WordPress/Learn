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
<p class="has-charcoal-4-color has-text-color has-link-color" style="margin-top:0"><?php esc_html_e( 'Dive into our learning pathways which will take you from beginner to expert at your own pace.', 'wporg-learn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:wporg-learn/learning-pathway-cards {"isMini":true,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} /-->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Featured Courses', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Courses take you through a series of lessons.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/courses/' ) ); ?>"><?php esc_html_e( 'See all Courses', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"perPage":6,"postType":"course","courseFeatured":true},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":3}} -->

		<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

			<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

				<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":1.6},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

				<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
		<p><?php esc_html_e( 'No featured courses found.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Lessons', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Lessons are short video tutorials that teach you about a specific topic.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/lessons/' ) ); ?>"><?php esc_html_e( 'See all Lessons', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":"6","pages":0,"offset":0,"postType":"lesson","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"parents":[]}} -->
<div class="wp-block-query">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":3}} -->

		<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

			<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

				<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

				<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}}}} /-->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
		<p><?php esc_html_e( 'No lessons found.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->
