<?php

/**
 * The archive page for Workshops.
 *
 * @package WPBBP
 */

get_header();?>

<div>
	<main class="site-main">
		<section>
			<div class="row align-middle between section-heading section-heading--with-button">
				<h2 class="section-heading_title"><?php esc_html_e( 'Workshops', 'wporg-learn' ); ?></h2>
				<a class="section-heading_link button button-large" href="/submit-workshop-idea"><?php esc_html_e( 'Submit Workshop Idea', 'wporg-learn' ); ?></a>
			</div>
			<hr>
			<?php get_template_part( 'template-parts/component', 'featured-workshop' ); ?>
			<?php get_template_part( 'template-parts/component', 'video-grid' ); ?>
		</section>
		<hr>

		<?php wporg_submit_idea_cta(); ?>
	</main>
</div>

<?php
get_footer();
