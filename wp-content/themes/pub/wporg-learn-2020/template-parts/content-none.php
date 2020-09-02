<?php
/**
 * The template part for displaying a message that posts cannot be found
 *
 * @package WordPressdotorg\Theme
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'wporg-learn' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p>
				<?php
				printf(
					/* translators: Link to post editor. */
					__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'wporg-learn' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'wporg-learn' ); ?></p>
			<div class="search-form--is-inline search-form--has-border">
				<?php get_search_form(); ?>
			</div>

		<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'wporg-learn' ); ?></p>
			<div class="search-form--is-inline search-form--has-border">
				<?php get_search_form(); ?>
			</div>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
