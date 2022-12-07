<?php
/**
 * Template part for displaying a single Idea post.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php the_title(); ?></h1>
		</div>

		<hr>

		<div class="idea-page">

			<div class="wp-block-columns idea-page_content">
				<div class="wp-block-column" style="flex-basis:66.66%">
					<?php the_content(); ?>
					<?php comments_template(); ?>
				</div>

				<div class="wp-block-column idea-page_sidebar" style="flex-basis:33.333%">
					<?php get_template_part( 'template-parts/component', 'ideas-details' ); ?>
					<?php get_template_part( 'template-parts/component', 'ideas-form' ); ?>
				</div> <!-- end sidebar column -->
			</div> <!-- end columns -->

		</div> <!-- end idea-page -->
	</section>
</article>
