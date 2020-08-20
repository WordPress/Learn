<?php
/**
 * Template part for displaying a list of videos in a grid.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

global $wp_query;
$query = $args['query'] ?? $wp_query;
?>

<?php if ( $query->have_posts() ) : ?>
	<ul class="row video-grid">
		<?php while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<?php get_template_part( 'template-parts/component', 'video-grid-item' ); ?>
		<?php endwhile; ?>
	</ul>
<?php endif; ?>
