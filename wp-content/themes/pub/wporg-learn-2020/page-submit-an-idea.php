<?php
/**
 * Template Name: Submit an Idea
 *
 * @package WPBBP
 */

get_header(); ?>

	<main id="main" class="site-main page-full-width" role="main">
		<?php echo get_template_part( 'template-parts/component', 'breadcrumbs' );  ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div id="submit-an-idea" class="type-page">
				<div class="entry-content">
					<section class="submit-idea-cta">
						<h4><?php _e( 'Is this a Workshop or Lesson Plan idea?' ); ?></h4>

						<div class="idea-type-lists">
							<div class="col">
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<p>
								Workshops are a collection of leesions and a great way to get people hands-on with WordPress.
								</p>
								<a class="button button-primary button-large" href="/submit-workshop-idea"><?php _e( 'Workshop Idea' ); ?></a>
							</div>
							
							<div class="col">
								<span class="dashicons dashicons-lightbulb"></span>
								<p>
								Lesson plans are designed to guide and inspire others to deliver great content.
								</p>
								<a class="button button-primary button-large" href="/submit-lesson-idea"><?php _e( 'Lesson Idea' ); ?></a>
							</div>
						</div>
					</section>
				</div>
			</div>
			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->

<?php
get_footer();
