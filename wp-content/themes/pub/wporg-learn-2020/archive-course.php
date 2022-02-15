<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

$prev_category = '';
$category = '';

get_header(); ?>

<main id="main" class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
		</div>

		<hr>

		<div class="card-grid card-grid_2">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) :
					the_post();

					$display_category = false;
					$categories = get_the_terms( get_the_ID(), 'course-category' );
					if ( isset( $categories[0] ) ) {
						$category = $categories[0]->name;
						if ( $category != $prev_category ) {
							$display_category = true;
							$prev_category = $category;
						}
					}

					if ( $display_category ) {
						echo '<h2 class="h4 course-category-header">' . esc_html( $category ) . '</h2>';
					}

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

<?php get_footer();
