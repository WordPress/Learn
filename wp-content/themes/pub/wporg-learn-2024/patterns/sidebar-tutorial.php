<?php
/**
 * Title: Sidebar Tutorial
 * Slug: wporg-learn-2024/sidebar-tutorial
 * Inserter: no
 *
 * @package wporg-learn-2024
 */

?>

<!-- wp:group {"align":"full","className":"wporg-learn-sidebar-meta-info","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wporg-learn-sidebar-meta-info">

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"textAlign":"center","width":100,"style":{"border":{"radius":"2px"},"spacing":{"padding":{"left":"13px","right":"13px","top":"16px","bottom":"16px"}},"typography":{"lineHeight":0,"fontStyle":"normal","fontWeight":"400"}},"className":"aligncenter is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
		<div class="wp-block-button has-custom-width wp-block-button__width-100 has-custom-font-size aligncenter is-style-outline has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:0">
			<a class="wp-block-button__link has-text-align-center wp-element-button" href="https://wordpress.org/playground/demo/?step=playground&amp;theme=twentytwentythree" style="border-radius:2px;padding-top:16px;padding-right:13px;padding-bottom:16px;padding-left:13px" target="_blank" rel="noreferrer noopener">
				<?php esc_html_e( 'Practice on a private demo site', 'wporg-learn' ); ?>
			</a>
		</div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

	<!-- wp:pattern {"slug":"wporg-learn-2024/sidebar-details"} /-->

</div>
<!-- /wp:group -->
