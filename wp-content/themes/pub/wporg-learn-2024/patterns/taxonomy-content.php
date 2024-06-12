<?php
/**
 * Title: Taxonomy Content
 * Slug: wporg-learn-2024/taxonomy-content
 * Inserter: no
 */

if ( ! is_tax() ) {
	return;
}

global $wp_query;

if ( isset( $wp_query->query_vars['wporg_learning_pathway'] ) ) {

	$learning_pathway_object = get_term_by( 'slug', $wp_query->query_vars['wporg_learning_pathway'], 'learning-pathway' );

	$learning_pathway_id = $learning_pathway_object->term_id;
	$learning_pathway_slug = $learning_pathway_object->slug;
	$learning_pathway_name = $learning_pathway_object->name;
	$learning_pathway_description = $learning_pathway_object->description;
	?>

	<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space","top":"0","bottom":"0"}},"border":{"bottom":{"color":"var:preset|color|light-grey-1","width":"1px"}}},"backgroundColor":"<?php echo esc_attr( $learning_pathway_slug ); ?>","className":"wporg-learn-tax-learning-pathway-header","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull has-<?php echo esc_attr( $learning_pathway_slug ); ?>-background-color has-background wporg-learn-tax-learning-pathway-header" style="border-bottom-color:var(--wp--preset--color--light-grey-1);border-bottom-width:1px;padding-top:0;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:0;padding-left:var(--wp--preset--spacing--edge-space)">

		<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"background":{"backgroundRepeat":"no-repeat","backgroundSize":"contain","backgroundPosition":"100% 50%"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"stretch"}} -->
		<div class="wp-block-group">
			
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"layout":{"selfStretch":"fill","flexSize":null}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

				<!-- wp:heading {"level":1,"fontSize":"heading-1","fontFamily":"eb-garamond"} -->
				<h1 class="wp-block-heading has-eb-garamond-font-family has-heading-1-font-size"><?php echo esc_html( $learning_pathway_name ); ?></h1>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"lineHeight":1.6}}} -->
				<p style="line-height:1.6"><?php echo esc_html( $learning_pathway_description ); ?></p>
				<!-- /wp:paragraph -->

			</div>
			<!-- /wp:group -->

			<!-- wp:group {"style":{"layout":{"selfStretch":"fixed","flexSize":"33%"},"background":{"backgroundImage":{"url":"<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/learning-pathway-' . $learning_pathway_slug . '.svg' ); ?>","source":"file"},"backgroundPosition":"0% 50%"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group"></div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

<?php } else { ?>

	<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"730px"}} -->
	<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:query-title {"type":"archive","showPrefix":false} /-->

	</div>
	<!-- /wp:group -->

<?php } ?>

<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--40);margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","width":290,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"wporg_learning_pathway":"<?php echo esc_attr( $learning_pathway_slug ); ?>"}} /-->

	<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
	<div class="wp-block-group wporg-query-filters">
		<!-- wp:wporg/query-filter {"key":"language"} /-->
		<!-- wp:wporg/query-filter {"key":"topic"} /-->
		<!-- wp:wporg/query-filter {"key":"level"} /-->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:query {"queryId":1,"query":{"perPage":12,"postType":"course","courseFeatured":false,"inherit":true},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid"} -->
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

</div>
<!-- /wp:group -->
