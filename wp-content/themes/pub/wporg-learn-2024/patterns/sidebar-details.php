<?php
/**
 * Title: Sidebar Details
 * Slug: wporg-learn-2024/sidebar-details
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

?>

<!-- wp:wporg-learn/sidebar-meta-list {"type":"course","fontSize":"normal"} /-->
 
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">

	<!-- wp:pattern {"slug":"wporg-learn-2024/content-feedback"} /-->

</div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">

	<!-- wp:heading {"level":2,"fontSize":"large"} -->
	<h2 class="wp-block-heading has-large-font-size">
		<?php esc_html_e( 'License', 'wporg-learn' ); ?>
	</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400,"lineHeight":"26px"}},"fontSize":"normal","fontFamily":"inter"} -->
	<p class="has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:26px">
		<?php echo wp_kses_post(
			sprintf(
				/* translators: 1: License link */
				__( '<a href="%s">CC BY-SA 4.0 ↗</a>', 'wporg-learn' ),
				'http://creativecommons.org/licenses/by-sa/4.0/',
			)
		); ?>
	</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
