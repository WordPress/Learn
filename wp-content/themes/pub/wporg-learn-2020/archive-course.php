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
$category_title = '';
$category_description = '';

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">

	<section>
		<div class="row align-middle between section-heading section-heading--with-space gutters">
			<?php the_archive_title( '<h1 class="section-heading_title h2 col-8">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
		</div>

		<hr>

			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) :
					the_post();

					$prev_category;
					$begin_new_category = false;
					$categories         = get_the_terms( get_the_ID(), 'course-category' );

					if ( isset( $categories[0] ) ) {
						$category_title       = $categories[0]->name;
						$category_description = $categories[0]->description;
						$begin_new_category   = $category_title !== $prev_category;
					}

					if ( $begin_new_category ) {
						// Close the previous card-grid if there was a previous category
						if ( ! empty( $prev_category ) ) {
							echo '</div>';
						}
						?>
		<h2 class="h4 course-category-header"><?php echo esc_html( $category_title ); ?></h2>
						<?php
						if ( $category_description ) {
							echo '<div class="course-category-description">' . esc_html( $category_description ) . '</div>';
						}
						?>
		<div class="card-grid card-grid_2">
						<?php
						$prev_category = $category_title;
					}

					get_template_part(
						'template-parts/component',
						'card',
						wporg_learn_get_card_template_args( get_the_ID() )
					);
				endwhile; ?>
		</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>

		<?php the_posts_pagination(); ?>
	</section>

	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
