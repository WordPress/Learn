<?php
/**
 * Title: Taxonomy Learning Pathway Content
 * Slug: wporg-learn-2024/taxonomy-learning-pathway-content
 * Inserter: no
 */

use function WordPressdotorg\Theme\Learn_2024\{get_learning_pathway_level_content};

$learning_pathway_object = get_queried_object();
$learning_pathway_id = $learning_pathway_object->term_id;
$learning_pathway_slug = $learning_pathway_object->slug;

$beginner_level_id = get_term_by( 'slug', 'beginner', 'level' )->term_id;
$intermediate_level_id = get_term_by( 'slug', 'intermediate', 'level' )->term_id;
$advanced_level_id = get_term_by( 'slug', 'advanced', 'level' )->term_id;

$content = get_learning_pathway_level_content( $learning_pathway_slug );
?>

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space","top":"0","bottom":"0"}},"border":{"bottom":{"color":"var:preset|color|light-grey-1","width":"1px"}}},"backgroundColor":"<?php echo esc_attr( $learning_pathway_slug ); ?>","className":"wporg-learn-tax-learning-pathway-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-<?php echo esc_attr( $learning_pathway_slug ); ?>-background-color has-background wporg-learn-tax-learning-pathway-header" style="border-bottom-color:var(--wp--preset--color--light-grey-1);border-bottom-width:1px;padding-top:0;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:0;padding-left:var(--wp--preset--spacing--edge-space)">

	<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"background":{"backgroundRepeat":"no-repeat","backgroundSize":"contain","backgroundPosition":"100% 50%"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"stretch"}} -->
	<div class="wp-block-group">
		
		<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"layout":{"selfStretch":"fill","flexSize":null}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

			<!-- wp:query-title {"type":"archive","showPrefix":false,"fontSize":"heading-1"} /-->

			<!-- wp:term-description {"style":{"typography":{"lineHeight":1.6}}} /-->

		</div>
		<!-- /wp:group -->

		<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"33%"},"background":{"backgroundImage":{"url":"<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/learning-pathway-' . $learning_pathway_slug . '.svg' ); ?>","source":"file"},"backgroundPosition":"0% 50%"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group"></div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|10"}}}} -->
	<h2 class="wp-block-heading" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['beginner']['title'] ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['beginner']['description'] ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/courses/?wporg_learning_pathway=' . $learning_pathway_slug . '&wporg_lesson_level=beginner' ) ); ?>"><?php esc_html_e( 'See all', 'wporg-learn' ); ?></a></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":0,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_attr( $beginner_level_id ); ?>],"learning-pathway":[<?php echo esc_attr( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid"} -->
	<div class="wp-block-query alignwide wporg-learn-course-grid">

		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

			<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

				<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

					<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":1.6},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

					<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

					<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
					<div class="wp-block-group">

						<!-- wp:wporg-learn/learning-duration {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

						<!-- wp:wporg-learn/lesson-count {"style":{"layout":{"selfStretch":"fill","flexSize":null}},"fontSize":"extra-small"} /-->

						<!-- wp:wporg-learn/course-status {"fontSize":"extra-small"} /-->

					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->

			<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results.","style":{"spacing":{"margin":{"top":"-20px"}}}} -->
			<p style="margin-top:-20px"><?php esc_html_e( 'No beginner pathways found.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

		<!-- /wp:query-no-results -->

	</div>
	<!-- /wp:query -->

	<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
	<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['intermediate']['title'] ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['intermediate']['description'] ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/courses/?wporg_learning_pathway=' . $learning_pathway_slug . '&wporg_lesson_level=intermediate' ) ); ?>"><?php esc_html_e( 'See all', 'wporg-learn' ); ?></a></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":1,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_html( $intermediate_level_id ); ?>],"learning-pathway":[<?php echo esc_html( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid"} -->
	<div class="wp-block-query alignwide wporg-learn-course-grid">

		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

			<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

				<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

					<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":1.6},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

					<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

					<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
					<div class="wp-block-group">

						<!-- wp:wporg-learn/learning-duration {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

						<!-- wp:wporg-learn/lesson-count {"style":{"layout":{"selfStretch":"fill","flexSize":null}},"fontSize":"extra-small"} /-->

						<!-- wp:wporg-learn/course-status {"fontSize":"extra-small"} /-->

					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->

			<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results.","style":{"spacing":{"margin":{"top":"-20px"}}}} -->
			<p style="margin-top:-20px"><?php esc_html_e( 'No intermediate pathways found.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

		<!-- /wp:query-no-results -->

	</div>
	<!-- /wp:query -->

	<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
	<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['advanced']['title'] ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['advanced']['description'] ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
		<p class="has-charcoal-4-color has-text-color has-link-color"><a href="<?php echo esc_url( site_url( '/courses/?wporg_learning_pathway=' . $learning_pathway_slug . '&wporg_lesson_level=advanced' ) ); ?>"><?php esc_html_e( 'See all', 'wporg-learn' ); ?></a></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":2,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_html( $advanced_level_id ); ?>],"learning-pathway":[<?php echo esc_html( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid"} -->
	<div class="wp-block-query alignwide wporg-learn-course-grid">

		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

			<!-- wp:group {"style":{"border":{"width":"1px","color":"var:preset|color|light-grey-1","radius":"2px"},"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group has-border-color has-white-background-color has-background" style="border-color:var(--wp--preset--color--light-grey-1);border-width:1px;border-radius:2px;min-height:100%">

				<!-- wp:post-featured-image {"style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"20px","right":"20px"}}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--20);padding-right:20px;padding-bottom:var(--wp--preset--spacing--20);padding-left:20px">

					<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":1.6},"spacing":{"margin":{"bottom":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"fontSize":"normal","fontFamily":"inter"} /-->

					<!-- wp:post-excerpt {"showMoreOnNewLine":false,"excerptLength":16,"style":{"spacing":{"margin":{"top":"var:preset|spacing|10"}},"typography":{"lineHeight":1.6}}} /-->

					<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"left"}} -->
					<div class="wp-block-group">

						<!-- wp:wporg-learn/learning-duration {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

						<!-- wp:wporg-learn/lesson-count {"style":{"layout":{"selfStretch":"fill","flexSize":null}},"fontSize":"extra-small"} /-->

						<!-- wp:wporg-learn/course-status {"fontSize":"extra-small"} /-->

					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->

			<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results.","style":{"spacing":{"margin":{"top":"-20px"}}}} -->
			<p style="margin-top:-20px"><?php esc_html_e( 'No advanced pathways found.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

		<!-- /wp:query-no-results -->

	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->
