<?php
/**
 * The template part for display a post card
 *
 * @package WordPressdotorg\Theme;
 */

namespace WordPressdotorg\Theme;

$args = wp_parse_args( $args );
$post_type_name = get_post_type();
$post_type_info = get_post_type_object( $post_type_name );

?>
<article class="post-card">
	<?php if ( ! in_array( $post_type_name, array( 'page', 'attachment', 'post' ) ) ) : ?>
	<div class="post-card_tag post-card_tag--<?php echo esc_attr( $post_type_name ); ?>">
		<?php echo esc_attr( $post_type_info->labels->singular_name ); ?>
	</div>
	<?php endif; ?>
	<div class="entry">
		<header class="entry-header">
			<h3 class="entry-title">
				<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h3>		
		</header><!-- .entry-header -->

		<div class="entry-excerpt">
			<p><?php the_excerpt(); ?></p>
		</div><!-- .entry-excerpt -->
	</div>		
	<?php
	// Use the post type:
	// If we don't have a template, don't show the footer
	$hyphened_post_type = str_replace( '_', '-', $post_type );
	get_template_part( 'template-parts/component', 'post-card-footer-' . hyphened_post_type );
	?>
</article>
