<?php
/**
 * Title: Learning Pathway section no results message
 * Slug: wporg-learn-2024/query-no-pathways
 * Inserter: no
 */

?>

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group">
	
	<!-- wp:group {"style":{"border":{"radius":"2px","width":"1px"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"borderColor":"light-grey-1","layout":{"type":"constrained"}} -->
	<div class="wp-block-group has-border-color has-light-grey-1-border-color" style="border-width:1px;border-radius:2px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)">

		<!-- wp:paragraph -->
		<p>
			<?php echo wp_kses_post(
				sprintf(
					/* translators: 1: Courses archive link */
					__( 'There’s nothing here yet. Check again soon or <a href="%s">find another course</a>.', 'wporg-learn' ),
					get_post_type_archive_link( 'course' ),
				)
			); ?>
		</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
