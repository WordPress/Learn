<?php get_header(); ?>

<main class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2 col-9">', '</h1>' ); ?>
			<a class="section-heading_link button button-large" href="https://learn.wordpress.org/workshop-presenter-application/"><?php esc_html_e( 'Submit Workshop Idea', 'wporg-learn' ); ?></a>
			<?php if ( is_tax() ) :
				$series_term = wporg_workshop_series_get_term( $post );
				?>
				<?php echo wp_kses_post( wpautop( term_description( $series_term->term_id ) ) ); ?>
			<?php endif; ?>
		</div>
		<hr>
		<?php // Only show the featured workshop on the first page of post type archives.
		if ( is_post_type_archive() && get_query_var( 'paged' ) < 2 ) : ?>
			<?php get_template_part( 'template-parts/component', 'featured-workshop' ); ?>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/component', 'video-grid' ); ?>

		<?php the_posts_pagination(); ?>
	</section>
	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
