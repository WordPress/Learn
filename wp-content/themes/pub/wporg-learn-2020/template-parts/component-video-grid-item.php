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
	<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink() ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wporg_get_post_thumbnail( $post, 'medium' );
		?>
		<h3><?php the_title(); ?></h3>
	</a>
</li>
