<?php
/**
 * Template Name: Workshop Ideas
 *
 * @package WPBBP
 */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main page-full-width">

		<?php
		while ( have_posts() ) {
			the_post();

			/*
			 * Old posts have the layout and presentation of meta data hardcoded into `post_content`, but
			 * newer posts only store prose there. Meta data, layout, etc is handled in
			 * `content-workshop-single.php`.
			 */
			$layout_hardcoded = has_block( 'core/columns' )
				&& has_block( 'wporg-learn/workshop-details' )
				&& has_block( 'core/separator' );

			if ( $layout_hardcoded ) {
				get_template_part( 'template-parts/content', 'workshop-single-hardcoded' );
			} else {
				get_template_part( 'template-parts/content', 'workshop-single' );
			}
		}
		?>
	</main><!-- #main -->

<?php
get_footer();
