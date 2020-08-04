<?php
/**
 * Template Name: Workshop Ideas
 *
 * @package WPBBP
 */

get_header(); 
?>

	<main id="main" class="site-main page-full-width" role="main">
	
		<?php echo get_template_part( 'template-parts/component', 'breadcrumbs' );  ?>

		<div class="type-page">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'single' );
			?>
		</div>

		<?php
			$tax_slugs = wporg_get_tax_slugs_from_workshop();
			$the_query = wporg_get_lesson_plans_by_tax_slugs_query( $tax_slugs );

			if ( $the_query->have_posts() ) {
		?>	

		<h2 class="h3"><?php _e( 'Workshop Lessons', 'wporg-learn' ); ?></h2>
		<div id="lesson-plans" class="lp-list">		
		<?php
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					get_template_part( 'template-parts/content', 'archive' );
				}		
			}
		?>
		</div>
			
		<?php endwhile; // End of the loop. ?>
	</main><!-- #main -->


<?php
get_footer();
