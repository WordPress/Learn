<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
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

				get_template_part( 'template-parts/content', 'page' );
			endwhile; // End of the loop.
			?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
