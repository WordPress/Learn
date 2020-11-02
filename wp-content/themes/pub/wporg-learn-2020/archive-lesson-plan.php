<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

get_header(); ?>

<main id="main" class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
		</div>
		<hr>
		<?php get_template_part( 'template-parts/component', 'lesson-filters' ); ?>

		<?php if ( have_posts() ) : ?>
			<div id="lesson-plans" class="lp-list">
				<?php while ( have_posts() ) :
					the_post(); ?>
					<?php get_template_part( 'template-parts/content', 'archive' ); ?>
				<?php endwhile; ?>
			</div>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>
				<?php echo esc_html( get_post_type_object( get_post_type() )->labels->not_found ); ?>
			</p>
		<?php endif; ?>
	</section>
	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
