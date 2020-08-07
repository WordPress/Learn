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
);

$options = get_query_var( 'video-grid-options' );

if( is_array( $options ) ) {
	$args = array_merge( $args, $options );
}

$featured = new \WP_Query( $args );

?>

<?php if ( $featured->have_posts() )  : ?>
	<ul class="row video-grid">
		<?php while ( $featured->have_posts() ) :
			$featured->the_post();
			get_template_part( 'template-parts/component', 'video-grid-item' );
		endwhile; ?>	
	</ul>
<?php endif; ?>
