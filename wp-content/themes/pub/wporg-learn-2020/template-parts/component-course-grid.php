<?php
/**
 * Template part for displaying a list of courses in a grid.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

global $wp_query;
$query = $args['query'] ?? $wp_query;
?>

<?php if ( $query->have_posts() ) : ?>
	<div class="course-grid card-grid card-grid_2">
		<?php while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<?php get_template_part( 'template-parts/component', 'card', wporg_learn_get_card_template_args( get_the_ID() ) ); ?>
		<?php endwhile; ?>
	</div>
<?php endif; ?>
