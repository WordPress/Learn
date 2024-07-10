<?php
/**
 * Title: Lesson Plan Archive Content
 * Slug: wporg-learn-2024/archive-lesson-plan-content
 * Inserter: no
 */

?>

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"750px"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading"><?php esc_html_e( 'Lesson Plans', 'wporg-learn' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Want to help others learn about WordPress? Read through, use, and remix these lesson plans.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignwide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search lesson plans', 'wporg-learn' ); ?>","width":290,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"<?php echo esc_attr( get_post_type() ); ?>"}} /-->

	<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
	<div class="wp-block-group wporg-query-filters">
		<!-- wp:wporg/query-filter {"key":"archive_language"} /-->
		<!-- wp:wporg/query-filter {"key":"archive_topic"} /-->
		<!-- wp:wporg/query-filter {"key":"archive_level","multiple":false} /-->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:query {"className":"wporg-learn-card-grid","queryId":1,"query":{"perPage":12,"pages":0,"offset":0,"postType":"lesson-plan","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"parents":[]}} -->
<div class="wp-block-query wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
		<p><?php esc_html_e( 'No lesson plans found.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->

		<!-- wp:query-pagination-previous {"label":"Previous"} /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next {"label":"Next"} /-->

	<!-- /wp:query-pagination -->

</div>
<!-- /wp:query -->
