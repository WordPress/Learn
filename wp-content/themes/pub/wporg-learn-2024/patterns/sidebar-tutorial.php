<?php
/**
 * Title: Sidebar Tutorial
 * Slug: wporg-learn-2024/sidebar-tutorial
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

?>

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"className":"wporg-learn-sidebar-meta-info","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group wporg-learn-sidebar-meta-info">

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		
		<!-- wp:button {"textAlign":"center","width":100,"style":{"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"},"spacing":{"padding":{"left":"var:preset|spacing|10","right":"var:preset|spacing|10"}}},"className":"aligncenter is-style-outline","fontFamily":"inter"} -->
		<div class="wp-block-button has-custom-width wp-block-button__width-100 aligncenter is-style-outline has-inter-font-family" style="font-style:normal;font-weight:400">
			<a class="wp-block-button__link has-text-align-center wp-element-button" href="https://wordpress.org/playground/demo/?step=playground&amp;theme=twentytwentythree" style="padding-right:var(--wp--preset--spacing--10);padding-left:var(--wp--preset--spacing--10)" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Practice on a private demo site', 'wporg-learn' ); ?></a>
		</div>
		<!-- /wp:button -->
	
	</div>
	<!-- /wp:buttons -->

	<!-- wp:pattern {"slug":"wporg-learn-2024/sidebar-details"} /-->

</div>
<!-- /wp:group -->
