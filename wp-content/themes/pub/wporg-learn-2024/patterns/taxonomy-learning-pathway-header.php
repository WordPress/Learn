<?php
/**
 * Title: Taxonomy Learning Pathway Header
 * Slug: wporg-learn-2024/taxonomy-learning-pathway-header
 * Inserter: no
 */

if ( ! is_tax( 'learning-pathway' ) ) {
	return;
}

$learning_pathway_object = get_queried_object();
$learning_pathway_slug = $learning_pathway_object->slug;
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

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","top":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space)">

	<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}}} -->
	<div id="wporg-search" class="wp-block-group alignwide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

		<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","width":290,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"wporg_learning_pathway":"<?php echo esc_attr( $learning_pathway_slug ); ?>"}} /-->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
