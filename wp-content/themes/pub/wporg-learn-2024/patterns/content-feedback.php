<?php
/**
 * Title: Content Feedback
 * Slug: wporg-learn-2024/content-feedback
 * Inserter: no
 */

?>

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">
	<?php esc_html_e( 'Suggestions', 'wporg-learn' ); ?>
</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400,"lineHeight":"26px"}},"fontSize":"normal","fontFamily":"inter"} -->
<p class="has-inter-font-family has-normal-font-size" style="font-style:normal;font-weight:400;line-height:26px">
	<?php echo wp_kses_post(
		sprintf(
			/* translators: 1: Report content feedback link */
			__( 'Found a typo, grammar error or outdated screenshot? <a href="%s">Contact us</a>.', 'wporg-learn' ),
			'https://learn.wordpress.org/report-content-feedback/',
		)
	); ?>
</p>
<!-- /wp:paragraph -->
