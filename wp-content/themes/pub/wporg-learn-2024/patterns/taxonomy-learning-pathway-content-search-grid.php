<?php
/**
 * Title: Taxonomy Learning Pathway Search Grid
 * Slug: wporg-learn-2024/taxonomy-learning-pathway-content-search-grid
 * Inserter: no
 */

?>

<!-- wp:wporg-learn/search-results-context {"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|20"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

<!-- wp:query {"queryId":1,"query":{"perPage":12,"postType":"course","courseFeatured":false,"inherit":true},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-course","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
		<p><?php esc_html_e( 'No pathways found.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->

		<!-- wp:query-pagination-previous {"label":"Previous"} /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next {"label":"Next"} /-->

	<!-- /wp:query-pagination -->

</div>
<!-- /wp:query -->
