<?php
/**
 * Title: Search Content
 * Slug: wporg-learn-2024/search-content
 * Inserter: no
 */

?>

<!-- wp:query {"queryId":0,"query":{"inherit":true,"perPage":25},"align":"wide"} -->
<div class="wp-block-query alignwide">

	<!-- wp:group {"className":"align-left","layout":{"type":"constrained","contentSize":"","justifyContent":"left"},"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-group align-left" style="margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:group {"align":"wide","className":"wporg-search-controls","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"top"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}} -->
		<div id="wporg-search" class="wp-block-group alignwide wporg-search-controls" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:var(--wp--preset--spacing--20)">
	
			<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search learning resources', 'wporg-learn' ); ?>","width":232,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"className":"is-style-secondary-search-control wporg-filtered-search-form"} /-->
		
		</div>
		<!-- /wp:group -->

		<!-- wp:wporg-learn/search-results-context {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}},"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}}},"textColor":"charcoal-4","fontSize":"small"} /-->

		<!-- wp:post-template {"align":"wide"} -->

			<!-- wp:post-title {"isLink":true} /-->

			<!-- wp:post-excerpt /-->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
		
			<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results.","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
			<p style="margin-top:var(--wp--preset--spacing--40)"><?php esc_attr_e( 'Sorry, but nothing matched your search terms.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

		<!-- /wp:query-no-results -->
	
	</div>
	<!-- /wp:group -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous {"label":"Previous"} /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next {"label":"Next"} /-->
	<!-- /wp:query-pagination -->

</div>
<!-- /wp:query -->
