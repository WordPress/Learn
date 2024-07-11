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
	$learning_pathway_slug = $wp_query->query_vars['wporg_learning_pathway'];
	?>

	<!-- wp:wporg-learn/learning-pathway-header {"align":"full","learningPathwaySlug":"<?php echo esc_attr( $learning_pathway_slug ); ?>"} /-->

<?php } ?>

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--60)">

	<?php if ( ! isset( $learning_pathway_slug ) ) { ?>

		<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"750px"}} -->
		<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--40)">

			<!-- wp:query-title {"type":"archive","showPrefix":true} /-->

		</div>
		<!-- /wp:group -->

		<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} -->
		<div class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--40);margin-bottom:var(--wp--preset--spacing--40)">

			<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","width":290,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true} /-->

			<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
			<div class="wp-block-group wporg-query-filters">
				<!-- wp:wporg/query-filter {"key":"language"} /-->
				<!-- wp:wporg/query-filter {"key":"topic"} /-->
				<!-- wp:wporg/query-filter {"key":"level","multiple":false} /-->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	<?php } ?>

	<!-- wp:wporg-learn/search-results-context {"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|20"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

	<!-- wp:query {"className":"wporg-learn-card-grid","queryId":1,"query":{"perPage":12,"postType":"course","courseFeatured":false,"inherit":true},"namespace":"wporg-learn/course-grid","align":"wide"} -->
	<div class="wp-block-query alignwide wporg-learn-card-grid">

		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

			<!-- wp:template-part {"slug":"card","className":"has-display-contents"} /-->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->

			<?php if ( isset( $learning_pathway_slug ) ) { ?>
				<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-pathways"} /-->
			<?php } else { ?>
				<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-results"} /-->
			<?php } ?>

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
