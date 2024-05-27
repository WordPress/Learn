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

				<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

				<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
				<div class="wp-block-group">

					<!-- wp:post-terms {"term":"level","separator":" ","className":"is-style-tag","fontSize":"extra-small"} /-->

				</div>
				<!-- /wp:group -->

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

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php esc_html_e( 'Upcoming Online Workshops', 'wporg-learn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php esc_html_e( 'Hands-on learning experiences where you can learn about a WordPress topic with fellow WordPress enthusiasts.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/online-workshops' ) ); ?>"><?php esc_html_e( 'See all Online Workshops', 'wporg-learn' ); ?></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:wporg-learn/upcoming-online-workshops {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50"}}}} /-->

<!-- wp:paragraph {"fontSize":"huge","fontFamily":"eb-garamond"} -->
<p class="has-eb-garamond-font-family has-huge-font-size">[TBD. Paragraph about inviting users to join the Training team to contribute with content creation]</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contribute">Contribute</a></div>
	<!-- /wp:button -->

	<!-- wp:button {"className":"is-style-outline"} -->
	<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://make.wordpress.org/training/handbook/"><?php esc_html_e( 'See the Training team\'s handbook', 'wporg-learn' ); ?></a></div>
	<!-- /wp:button -->

</div>
<!-- /wp:buttons -->

<!-- wp:group {"align":"full","style":{"border":{"bottom":{"color":"var:preset|color|white-opacity-15","width":"1px"}},"spacing":{"padding":{"right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space"}}},"backgroundColor":"charcoal-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-charcoal-2-background-color has-background" style="border-bottom-color:var(--wp--preset--color--white-opacity-15);border-bottom-width:1px;padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space)">

	<!-- wp:columns {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"blockGap":{"left":"0"}}},"textColor":"white"} -->
	<div class="wp-block-columns has-white-color has-text-color has-link-color">

		<!-- wp:column {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","right":"var:preset|spacing|edge-space"}}}} -->
		<div class="wp-block-column" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--50)">

			<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0"}}},"fontSize":"huge","fontFamily":"eb-garamond"} -->
			<h2 class="wp-block-heading has-eb-garamond-font-family has-huge-font-size" style="margin-top:0;font-style:normal;font-weight:400">TBD heading</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size"><a href="https://wordpress.org/documentation"><?php esc_html_e( 'Documentation', 'wporg-learn' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size"><a href="https://developer.wordpress.org"><?php esc_html_e( 'Developer Resources', 'wporg-learn' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size"><a href="https://wordpress.org/support/forums"><?php esc_html_e( 'Support Forums', 'wporg-learn' ); ?></a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|edge-space","right":"0"}},"border":{"left":{"color":"var:preset|color|white-opacity-15","width":"1px"},"top":{},"right":{},"bottom":{}}},"className":"wporg-learn-front-gage-signup"} -->
		<div class="wp-block-column wporg-learn-front-gage-signup" style="border-left-color:var(--wp--preset--color--white-opacity-15);border-left-width:1px;padding-top:var(--wp--preset--spacing--50);padding-right:0;padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--edge-space)">

			<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"},"spacing":{"margin":{"top":"0"}}},"fontSize":"huge","fontFamily":"eb-garamond"} -->
			<h2 class="wp-block-heading has-eb-garamond-font-family has-huge-font-size" style="margin-top:0;font-style:normal;font-weight:400">TBD heading</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Sign up for the free Learn WordPress newsletter and get a monthly dose of what\'s new in the world of WordPress courses, lessons, and online workshops.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
