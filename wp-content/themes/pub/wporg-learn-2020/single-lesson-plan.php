<?php
/**
 * The template for displaying all single post or CPT entry.
 *
 * @package WPBBP
 */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main type-page">

		<div id="main-content">
			<?php

			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content', 'single', array( 'context' => 'lesson-plan' ) );
			endwhile; // End of the loop.
			?>
		</div>
	</main><!-- #main -->

<?php
get_footer();
