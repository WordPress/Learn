<?php
/**
 * Title: Site Breadcrumbs
 * Slug: wporg-learn-2024/site-breadcrumbs
 * Inserter: no
 */

// Ensure breadcrumbs are displayed only when there are at least 3 levels.
$block_content = '<!-- wp:wporg/site-breadcrumbs {"fontSize":"small"} /-->';
$parsed_content = do_blocks( $block_content );
preg_match_all( '/<span\b[^>]*>/', $parsed_content, $matches );
$breadcrumb_level = count( $matches[0] );
$show_breadcrumbs = $breadcrumb_level >= 3;

?>

<?php if ( $show_breadcrumbs ) : ?>
	<!-- wp:group {"className":"wporg-breadcrumbs","align":"full","style":{"spacing":{"padding":{"top":"18px","bottom":"18px","left":"var:preset|spacing|edge-space","right":"var:preset|spacing|edge-space"}}},"backgroundColor":"white","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wporg-breadcrumbs wp-block-group alignfull has-white-background-color has-background" style="padding-top:18px;padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:18px;padding-left:var(--wp--preset--spacing--edge-space)">
		<!-- wp:wporg/site-breadcrumbs {"fontSize":"small"} /-->
	</div>
	<!-- /wp:group -->
<?php endif; ?>
