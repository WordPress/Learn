<?php
/**
 * Title: Activity Kits Archive Content
 * Slug: wporg-learn-2024/archive-activity-kits-content
 * Inserter: no
 */

?>

<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"left","contentSize":"750px"}} -->
<div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading"><?php esc_html_e( 'Activity Library', 'wporg-learn' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Download ready-to-use WordPress activity kits designed for educators, meetup organizers, and community facilitators. Each kit includes everything you need to run a hands-on WordPress learning session.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group alignwide" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--50)">

	<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search activity kits', 'wporg-learn' ); ?>","width":290,"widthUnit":"px","buttonText":"<?php esc_attr_e( 'Search', 'wporg-learn' ); ?>","buttonPosition":"button-inside","buttonUseIcon":true,"query":{"post_type":"activity_kit"}} /-->

	<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","flexWrap":"nowrap"},"className":"wporg-query-filters"} -->
	<div class="wp-block-group wporg-query-filters">
		<!-- wp:wporg/query-filter {"key":"activity_kit_topic"} /-->
		<!-- wp:wporg/query-filter {"key":"activity_kit_level","multiple":false} /-->
		<!-- wp:wporg/query-filter {"key":"archive_language"} /-->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":12,"postType":"activity_kit","inherit":true},"namespace":"wporg/activity-kits","align":"wide","className":"wporg-learn-activity-kits-grid wporg-learn-card-grid"} -->
<div class="wp-block-query alignwide wporg-learn-activity-kits-grid wporg-learn-card-grid">

	<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"330px"}} -->

		<!-- wp:wporg/activity-kit-card /-->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->

		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'No activity kits found. Try adjusting your filters.', 'wporg-learn' ); ?></p>
		<!-- /wp:paragraph -->

	<!-- /wp:query-no-results -->

	<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->

		<!-- wp:query-pagination-previous {"label":"Previous"} /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next {"label":"Next"} /-->

	<!-- /wp:query-pagination -->

</div>
<!-- /wp:query -->

<!-- wp:group {"align":"wide","style":{"border":{"width":"1px","style":"solid","color":"var(--wp--custom--color--border)"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="border-color:var(--wp--custom--color--border);border-style:solid;border-width:1px;margin-top:var(--wp--preset--spacing--60);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

	<!-- wp:heading {"textAlign":"center","level":2} -->
	<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Help create activity kits', 'wporg-learn' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","style":{"layout":{"selfStretch":"fit","flexSize":null}},"fontSize":"normal"} -->
	<p class="has-text-align-center"><?php esc_html_e( 'Behind every activity kit is a group of educators and community members working to make WordPress learning more accessible. If you\'d like to contribute a kit or improve an existing one, get involved with the Training team.', 'wporg-learn' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://make.wordpress.org/training/"><?php esc_html_e( 'Get involved', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://make.wordpress.org/training/handbook/wordpress-activity-kits/"><?php esc_html_e( 'Read the handbook', 'wporg-learn' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
