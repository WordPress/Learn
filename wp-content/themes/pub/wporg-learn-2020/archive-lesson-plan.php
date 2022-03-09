<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">

	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
		</div>

		<hr>

		<div class="row gutters between">
			<div class="card-grid col-9">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) :
						the_post();
						get_template_part(
							'template-parts/component',
							'card',
							wporg_learn_get_card_template_args( get_the_ID() )
						);
					endwhile; ?>
				<?php else : ?>
					<p class="not-found">
						<?php echo esc_html( get_post_type_object( 'lesson-plan' )->labels->not_found ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php get_template_part( 'template-parts/component', 'lesson-filters' ); ?>
		</div>

		<?php the_posts_pagination(); ?>
	</section>
	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
