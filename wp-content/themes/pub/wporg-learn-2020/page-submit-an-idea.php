<?php
/**
 * Template Name: Submit an Idea
 *
 * @package WPBBP
 */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main page-full-width">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div id="submit-an-idea" class="type-page">
				<div class="entry-content">
					<section class="submit-idea-cta">
						<h2><?php esc_html_e( 'Is this a Workshop or Lesson Plan idea?', 'wporg-learn' ); ?></h2>

						<div class="idea-type-lists">
							<div class="col">
								<span class="dashicons dashicons-welcome-learn-more"></span>
								<p><?php esc_html_e( 'Workshops are a collection of lessons and a great way to get people hands-on with WordPress.', 'wporg-learn' ); ?></p>
								<a class="button button-primary button-large" href="/submit-workshop-idea"><?php esc_html_e( 'Workshop Idea', 'wporg-learn' ); ?></a>
							</div>
							
							<div class="col">
								<span class="dashicons dashicons-lightbulb"></span>
								<p><?php esc_html_e( 'Lesson plans are designed to guide and inspire others to deliver great content.', 'wporg-learn' ); ?></p>
								<a class="button button-primary button-large" href="/submit-lesson-idea"><?php esc_html_e( 'Lesson Idea', 'wporg-learn' ); ?></a>
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
