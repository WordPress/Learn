<?php
/**
 * Content for auto-generated workshop posts.
 *
 * Note that if the template for the workshop post type changes, this will need to be updated as well.
 */

/** @var array $blurbs */
?>
<!-- wp:core-embed/wordpress-tv {"className":"workshop-page_video"} /-->

<!-- wp:columns {"className":"workshop-page_content"} -->
<div class="wp-block-columns workshop-page_content">
	<!-- wp:column {"width":66.66} -->
	<div class="wp-block-column" style="flex-basis:66.66%">
		<?php echo $blurbs['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<!-- wp:heading {"level":"2"} -->
		<h2><?php esc_html_e( 'Learning outcomes', 'wporg-learn' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:list {"ordered":true,"className":"workshop-page_list"} -->
		<ol class="workshop-page_list">
			<?php echo $blurbs['learning-objectives']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</ol>
		<!-- /wp:list -->

		<!-- wp:heading {"level":"2"} -->
		<h2><?php esc_html_e( 'Comprehension questions', 'wporg-learn' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:list {"className":"workshop-page_list"} -->
		<ul class="workshop-page_list">
			<?php echo $blurbs['comprehension-questions']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</ul>
		<!-- /wp:list -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":33.333,"className":"workshop-page_sidebar"} -->
	<div class="wp-block-column workshop-page_sidebar" style="flex-basis:33.333%">
		<!-- wp:wporg-learn/workshop-details /-->

		<!-- wp:button {"borderRadius":5,"className":"is-style-secondary-full-width"} -->
		<div class="wp-block-button is-style-secondary-full-width">
			<a class="wp-block-button__link" href="https://www.meetup.com/learn-wordpress-discussions/events/" style="border-radius:5px">
				<?php esc_html_e( 'Join a Group Discussion', 'wporg-learn' ); ?>
			</a>
		</div>
		<!-- /wp:button -->

		<!-- wp:paragraph {"className":"terms"} -->
		<p class="terms">
			<?php
			printf(
				wp_kses_post( __( 'You must agree to our <a href="%s">Code of Conduct</a> in order to participate.', 'wporg-learn' ) ),
				'https://learn.wordpress.org/code-of-conduct/'
			);
			?>
		</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:separator -->
<hr class="wp-block-separator"/>
<!-- /wp:separator -->
