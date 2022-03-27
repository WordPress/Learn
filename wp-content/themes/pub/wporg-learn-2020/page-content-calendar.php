<?php
/**
 * Template Name: Content Calendar
 *
 * @package WPBBP
 */

namespace WordPressdotorg\Theme;

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main">

		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
		</div>
		<hr>

		<div id="main-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>

				<div id="content-calendar" class="type-page">
					<div class="entry-content">
						
					</div>
				</div>

				<?php
			endwhile; // End of the loop.
			?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
