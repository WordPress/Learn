<?php
/**
 * Title: Taxonomy Learning Pathway Sections
 * Slug: wporg-learn-2024/taxonomy-learning-pathway-content-sections
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

$learning_pathway_id = $learning_pathway_object->term_id;
$learning_pathway_slug = $learning_pathway_object->slug;
$learning_pathway_url = get_term_link( $learning_pathway_object );

$beginner_level_id = get_term_by( 'slug', 'beginner', 'level' )->term_id;
$intermediate_level_id = get_term_by( 'slug', 'intermediate', 'level' )->term_id;
$advanced_level_id = get_term_by( 'slug', 'advanced', 'level' )->term_id;

$content = get_learning_pathway_level_content( $learning_pathway_slug );
?>

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['beginner']['title'] ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}},"layout":{"selfStretch":"fixed","flexSize":"750px"}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['beginner']['description'] ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color">
		<a href="<?php echo esc_url( $learning_pathway_url . '?wporg_lesson_level=beginner' ); ?>" aria-label="<?php echo esc_attr( $content['beginner']['see_all_aria_label'] ); ?>"><?php esc_html_e( 'See all beginner', 'wporg-learn' ); ?></a>
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":0,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_attr( $beginner_level_id ); ?>],"learning-pathway":[<?php echo esc_attr( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-course-h3","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-pathways"} /-->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['intermediate']['title'] ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}},"layout":{"selfStretch":"fixed","flexSize":"750px"}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['intermediate']['description'] ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color">
		<a href="<?php echo esc_url( $learning_pathway_url . '?wporg_lesson_level=intermediate' ); ?>" aria-label="<?php echo esc_attr( $content['intermediate']['see_all_aria_label'] ); ?>"><?php esc_html_e( 'See all intermediate', 'wporg-learn' ); ?></a>
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_html( $intermediate_level_id ); ?>],"learning-pathway":[<?php echo esc_html( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-course-h3","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-pathways"} /-->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|10"}}}} -->
<h2 class="wp-block-heading" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--10)"><?php echo esc_html( $content['advanced']['title'] ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|charcoal-4"}}},"layout":{"selfStretch":"fixed","flexSize":"750px"}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color"><?php echo esc_html( $content['advanced']['description'] ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|blueberry-1"}}}},"textColor":"charcoal-4"} -->
	<p class="has-charcoal-4-color has-text-color has-link-color">
		<a href="<?php echo esc_url( $learning_pathway_url . '?wporg_lesson_level=advanced' ); ?>" aria-label="<?php echo esc_attr( $content['advanced']['see_all_aria_label'] ); ?>"><?php esc_html_e( 'See all advanced', 'wporg-learn' ); ?></a>
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":2,"query":{"perPage":3,"postType":"course","courseFeatured":false,"taxQuery":{"level":[<?php echo esc_html( $advanced_level_id ); ?>],"learning-pathway":[<?php echo esc_html( $learning_pathway_id ); ?>]},"inherit":false},"namespace":"wporg-learn/course-grid","align":"wide","className":"wporg-learn-course-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-course-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:template-part {"slug":"card-course-h3","className":"has-display-contents"} /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:pattern {"slug":"wporg-learn-2024/query-no-pathways"} /-->

	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->
