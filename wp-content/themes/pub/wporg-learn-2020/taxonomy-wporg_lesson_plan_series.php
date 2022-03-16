<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

get_header(); ?>

	<main id="main" class="site-main type-page">
		<section>
			<div class="row align-middle between section-heading section-heading--with-space">
				<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			</div>

			<hr>
			<div class="card-grid card-grid_2">
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
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</div>
			<?php the_posts_pagination(); ?>
		</section>

		<hr>

		<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
	</main>
<?php
get_footer();
