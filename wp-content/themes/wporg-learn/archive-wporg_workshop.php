<?php

/**
 * The archive page for Workshops.
 *
 * @package WPBBP
 */

get_header();?>

<div>
	<main class="site-main">
		<h2><?php esc_html_e( 'Workshops', 'wporg-learn' ); ?></h2>
		<hr>
		<?php get_template_part( 'template-parts/component', 'video-grid' ); ?>
	</main>
</div>

<?php
get_footer();
