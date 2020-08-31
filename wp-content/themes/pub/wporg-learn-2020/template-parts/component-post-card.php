<?php
/**
 * The template part for display a post card
 *
 * @package WordPressdotorg\Theme;
 */

namespace WordPressdotorg\Theme;

$args = wp_parse_args( $args );
$item = get_post_type_object( get_post_type() );

?>
<article class="post-card">
	<div class="post-card_tag post-card_tag--<?php echo $item->name; ?>">
		<?php echo $item->labels->singular_name; ?>
	</div>
	<div class="entry">
		<header class="entry-header">
			<h3 class="entry-title">
				<a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark"><?php the_title(); ?></a>
			</h3>		
		</header><!-- .entry-header -->

		<div class="entry-excerpt">
			<p><?php the_excerpt(); ?></p>
		</div><!-- .entry-excerpt -->
	</div>
	<footer>
		<?php get_template_part( 'template-parts/component', 'post-card-footer' ); ?>
	</footer>

	<?php
		edit_post_link(
			sprintf(
				/* translators: %s: Name of current post */
				__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'wporg' ),
				get_the_title()
			),
			'<span class="edit-link">',
			'</span>'
		);
		?>
</article>