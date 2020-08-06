<?php
/**
 * Template Name: Workshop Ideas
 *
 * @package WPBBP
 */

get_header(); 
?>

	<main id="main" class="site-main page-full-width" role="main">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content', 'workshop-single' );
		}	?>
	</main><!-- #main -->

<?php
get_footer();