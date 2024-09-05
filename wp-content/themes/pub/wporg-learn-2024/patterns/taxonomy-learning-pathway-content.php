<?php
/**
 * Title: Taxonomy Learning Pathway Content
 * Slug: wporg-learn-2024/taxonomy-learning-pathway-content
 * Inserter: no
 */

use function WordPressdotorg\Theme\Learn_2024\{get_learning_pathway_level_content};

if ( ! is_tax( 'learning-pathway' ) ) {
	return;
}

$learning_pathway_object = get_queried_object();

if ( ! $learning_pathway_object ) {
	return;
}

global $wp_query;
$search_term = $wp_query->get( 's' );
?>

<!-- wp:wporg-learn/learning-pathway-header {"align":"full","learningPathwaySlug":"<?php echo esc_attr( $learning_pathway_object->slug ); ?>"} /-->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--60)">

	<?php if ( $search_term ) { ?>

		<!-- wp:pattern {"slug":"wporg-learn-2024/taxonomy-learning-pathway-content-search-grid"} /-->

	<?php } else { ?>

		<!-- wp:pattern {"slug":"wporg-learn-2024/taxonomy-learning-pathway-content-sections"} /-->

	<?php } ?>

</div>
<!-- /wp:group -->
