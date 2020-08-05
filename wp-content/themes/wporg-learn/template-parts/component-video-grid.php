<?php
/**
 * Template part for displaying a list of videos in a grid.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$args = array(
	'post_type' => 'wporg_workshop',
	'category_name' => 'featured',
);

$featured = new \WP_Query( $args );

?>

<?php if ( $featured->have_posts() )  : ?>
	<section>
		<div class="row align-middle between section-heading">
			<h2 class="h4 section-heading_title"><?php esc_html_e( 'Recent Workshops', 'wporg-learn' ); ?></h2>
			<a class="section-heading_link" href="/workshops"><?php _e( 'View All »' ); ?></a>
		</div>
	
		<ul class="row gutters between video-grid">
			<?php while ( $featured->have_posts() ) :
				$featured->the_post();
				get_template_part( 'template-parts/component', 'video-grid-item' );
			endwhile; ?>	
		</ul>
	</section>
<?php endif; ?>
