<?php
/**
 * Template Name: Online Workshops
 *
 * @package WPBBP
 */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main page-full-width">

	<section>
		<div class="section-heading section-heading--with-space">
			<?php the_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
		</div>

		<hr>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div class="section-intro">
				<div class="row between gutters">
					<p class="col-8"><?php esc_html_e( 'Online workshops are live sessions where you can learn alongside other WordPress enthusiasts. They are a safe zone where you can come as you are, develop new ideas, explore issues, ask questions, network over shared interests, exchange theories, collaborate on work, and thrive in uncertainty.', 'wporg-learn' ); ?></p>
					<div class="col-4">
						<div class="wp-block-button is-style-secondary-full-width">
							<a class="wp-block-button__link" href="#apply-to-facilitate" style="border-radius:5px"><?php esc_html_e( 'Apply to facilitate', 'wporg-learn' ); ?></a>
						</div>
						<div class="wp-block-button is-style-secondary-full-width">
							<a class="wp-block-button__link" href="https://wordpress.tv/category/learn-wordpress-online-workshops/" target="_blank" style="border-radius:5px"><?php esc_html_e( 'View recorded online workshops', 'wporg-learn' ); ?></a>
						</div>
					</div>
				</div>
			</div>

			<hr>

			<div id="main-content">
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
			</div>
			<?php
		endwhile; // End of the loop.
		?>
	</section>

</main><!-- #main -->

<?php
get_footer();
