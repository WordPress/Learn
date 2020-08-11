<?php
/**
 * Template part for displaying a list of videos in a grid.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$options = get_query_var( 'video-grid-options' );

if ( is_array( $options ) ) {
	$latest_posts = wporg_get_workshops( $options );
} else {
	$latest_posts = wporg_get_workshops();
}

?>

<?php if ( $latest_posts->have_posts() ) : ?>
	<ul class="row video-grid">
		<?php while ( $latest_posts->have_posts() ) :
			$latest_posts->the_post();
			get_template_part( 'template-parts/component', 'video-grid-item' );
		endwhile; ?>	
	</ul>
<?php endif; ?>
