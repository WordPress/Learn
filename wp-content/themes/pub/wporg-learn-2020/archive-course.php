<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

$all_courses        = array();
$course_categories  = array();
$prev_category      = '';
$begin_new_category = false;

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">
	<section>
		<div class="row align-middle between section-heading">
			<div class="section-heading section-heading--with-space">
				<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			</div>
			<?php if ( is_user_logged_in() ) : ?>
				<a class="section-heading_link" href="/my-courses/"><span><?php esc_html_e( 'My Courses', 'wporg-learn' ); ?></span></a>
			<?php endif; ?>
		</div>
		<hr>

		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) :
				the_post();

				$categories = get_the_terms( get_the_ID(), 'course-category' );

				if ( isset( $categories[0] ) ) {
					$category_slug        = $categories[0]->slug;
					$category_title       = $categories[0]->name;
					$category_description = $categories[0]->description;

					ob_start();
					get_template_part(
						'template-parts/component',
						'card',
						wporg_learn_get_card_template_args( get_the_ID() )
					);

					$all_courses[ $category_slug ]['name']        = $category_title;
					$all_courses[ $category_slug ]['description'] = $category_description;
					$all_courses[ $category_slug ]['courses'][]   = ob_get_clean();
				}

			endwhile;

			if ( ! empty( $all_courses ) ) { ?>
				<div class="section-intro">
					<div class="row between gutters">
						<p class="col-8"><?php esc_html_e( 'WordPress provides limitless ways for people to craft and grow their online presence. The content in these courses is delivered in multiple formats, with a focus on text and video, working towards practical learning objectives to help you become a better WordPress developer, designer, user, and contributor.', 'wporg-learn' ); ?></p>
						<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
					</div>
				</div>
				<nav class="section-nav">
					<ul>
					<?php foreach ( $all_courses as $slug => $category ) { ?>
						<li class="section-nav-item">
							<a href="#<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $category['name'] ); ?></a>
						</li>
					<?php } ?>
					</ul>
				</nav>
				<hr>
				<?php
				foreach ( $all_courses as $slug => $category ) {

					// Check for new category on each loop and output necessary content and markup
					$begin_new_category = $slug !== $prev_category;

					if ( $begin_new_category ) {

						// Close the previous card-grid if there was a previous category
						if ( ! empty( $prev_category ) ) {
							echo '</div><hr>';
						}

						// Print category title and description
						echo '<h2 class="h4 course-category-header" id="' . esc_attr( $slug ) . '">' . esc_html( $category['name'] ) . '</h2>';
						if ( $category_description ) {
							echo '<div class="course-category-description">' . esc_html( $category['description'] ) . '</div>';
						}

						// Start a new card grid for the new category
						echo '<div class="card-grid card-grid_2">';

						// Set variable to check for new category on next loop
						$prev_category = $slug;
					}

					// Display card for each course
					foreach ( $category['courses'] as $course ) {
						echo wp_kses( $course, 'post' );
					}
				}

				echo '</div>';

			} ?>

			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>

		<?php the_posts_pagination(); ?>
	</section>

	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
