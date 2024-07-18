<?php
/**
 * Title: Footer Content
 * Slug: wporg-learn-2024/footer-content
 * Inserter: no
 */

?>

<!-- wp:group {"align":"full","style":{"border":{"bottom":{"color":"var:preset|color|white-opacity-15","width":"1px"}},"spacing":{"padding":{"right":"var:preset|spacing|edge-space","left":"var:preset|spacing|edge-space"}}},"backgroundColor":"charcoal-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-charcoal-2-background-color has-background" style="border-bottom-color:var(--wp--preset--color--white-opacity-15);border-bottom-width:1px;padding-right:var(--wp--preset--spacing--edge-space);padding-left:var(--wp--preset--spacing--edge-space)">

	<!-- wp:columns {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"blockGap":{"left":"0"}}},"textColor":"white"} -->
	<div class="wp-block-columns has-white-color has-text-color has-link-color">

		<!-- wp:column {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","right":"var:preset|spacing|edge-space"}}}} -->
		<div class="wp-block-column" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--edge-space);padding-bottom:var(--wp--preset--spacing--50)">

			<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"30px"},"spacing":{"margin":{"top":"0"}}},"fontFamily":"eb-garamond"} -->
			<h2 class="wp-block-heading has-eb-garamond-font-family" style="margin-top:0;font-size:30px;font-style:normal;font-weight:400"><?php esc_html_e( 'More helpful resources', 'wporg-learn' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:list {"className":"is-style-links-list","fontSize":"large"} -->
			<ul class="is-style-links-list has-large-font-size">
				
				<!-- wp:list-item -->
				<li><a href="https://wordpress.org/documentation"><?php esc_html_e( 'Documentation', 'wporg-learn' ); ?></a></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><a href="https://developer.wordpress.org"><?php esc_html_e( 'Developer Resources', 'wporg-learn' ); ?></a></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><a href="https://wordpress.org/support/forums"><?php esc_html_e( 'Support Forums', 'wporg-learn' ); ?></a></li>
				<!-- /wp:list-item -->
			
			</ul>
			<!-- /wp:list -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|edge-space","right":"0"}},"border":{"left":{"color":"var:preset|color|white-opacity-15","width":"1px"},"top":{},"right":{},"bottom":{}}},"className":"wporg-learn-footer-signup"} -->
		<div class="wp-block-column wporg-learn-footer-signup" style="border-left-color:var(--wp--preset--color--white-opacity-15);border-left-width:1px;padding-top:var(--wp--preset--spacing--50);padding-right:0;padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--edge-space)">

			<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"30px"},"spacing":{"margin":{"top":"0"}}},"fontFamily":"eb-garamond"} -->
			<h2 class="wp-block-heading has-eb-garamond-font-family" style="margin-top:0;font-size:30px;font-style:normal;font-weight:400"><?php esc_html_e( 'Sign up for updates', 'wporg-learn' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Get the latest news about everything Learn WordPress. Find out when new courses and lessons are available, and when registration opens for upcoming Online Workshops.', 'wporg-learn' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:jetpack/subscriptions {"className":"is-style-compact"} /-->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
