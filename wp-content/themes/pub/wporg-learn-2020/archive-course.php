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
$courses_list = '';
$course_categories = array();

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
						$category_slug        = $categories[0]->slug;
						$category_description = $categories[0]->description;
						$begin_new_category   = $category_title !== $prev_category;
					}

					if ( $begin_new_category ) {

						$course_categories[ $category_slug ] = $category_title;

						// Close the previous card-grid if there was a previous category
						if ( ! empty( $prev_category ) ) {
							$courses_list .= '</div>';
						}
						$courses_list .= '<h2 class="h4 course-category-header" id="' . esc_attr( $category_slug ) . '">' . esc_html( $category_title ) . '</h2>';

						if ( $category_description ) {
							$courses_list .=  '<div class="course-category-description">' . esc_html( $category_description ) . '</div>';
						}
						$courses_list .= '<div class="card-grid card-grid_2">';
						$prev_category = $category_title;
					}

					ob_start();
					get_template_part(
						'template-parts/component',
						'card',
						wporg_learn_get_card_template_args( get_the_ID() )
					);
					$courses_list .= ob_get_clean();

				endwhile; ?>

				<?php
				$courses_list .= '</div>';

				if( ! empty( $course_categories ) ) { ?>
					<p>
						<br>
						<?php _e( 'WordPress provides limitless ways for people to craft and grow their online presence. The content in these courses is delivered in multiple formats, with a focus on text and video, working towards practical learning objectives to help you become a better WordPress developer, designer, user, and contributor.', 'wporg-learn' );
						?>
					</p>
					<p>
						<?php
						$i = 0;
						foreach ( $course_categories as $slug => $title ) {
							if ( 0 < $i ) { echo ' | '; }
							echo '<a href="#' . esc_attr( $slug ) . '">' . esc_html( $title ) . '</a>';
							$i++;
						}
						?>
					</p>

					<hr>
				<?php }

				echo $courses_list;

				?>

			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>

		<?php the_posts_pagination(); ?>
	</section>

	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
