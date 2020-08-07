<?php
/**
 * Template part for displaying an item in a video grid.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<li class="col-4 video-grid_item">
	<?php if( has_post_thumbnail() ) : ?>
		<?php echo the_post_thumbnail( 'medium' ); ?>
	<?php else : ?>
		<div class="video-grid_item--no-image"></div>
	<? endif; ?>
	<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
</li>