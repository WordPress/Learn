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
					<p class="col-8"><?php esc_html_e( 'WordPress provides limitless ways for people to craft and grow their online presence. The content in these courses is delivered in multiple formats, with a focus on text and video, working towards practical learning objectives to help you become a better WordPress developer, designer, user, and contributor.', 'wporg-learn' ); ?></p>
					<?php // get_template_part( 'template-parts/component', 'archive-search' ); ?>
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
