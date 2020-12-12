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
?>

	<main id="main" class="site-main" role="main">
		<?php get_template_part( 'template-parts/component', 'breadcrumbs' ); ?>

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
