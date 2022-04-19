<?php

namespace WordPressdotorg\Theme;

/** @var array $args */

$current_post_type = get_post_type();
$classes = implode( ' ', get_post_class( $args['class'] ) );
?>

<article class="card <?php echo esc_attr( $classes ); ?>">
	<?php if ( is_search() && in_array( $current_post_type, array( 'lesson-plan', 'wporg_workshop', 'course' ) ) ) : ?>
		<div class="card-type card-type--<?php echo esc_attr( $current_post_type ); ?>">
			<?php echo esc_attr( get_post_type_object( $current_post_type )->labels->singular_name ); ?>
		</div>
	<?php endif; ?>

	<div class="card-entry">
		<header class="card-header">
			<h3 class="card-title h4">
				<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h3>
		</header>

		<div class="card-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<footer class="card-footer">
			<?php if ( ! empty( $args['meta'] ) ) : ?>
				<ul class="card-meta">
					<?php foreach ( $args['meta'] as $list_item ) : ?>
						<?php get_template_part( 'template-parts/component', 'card-meta-item', $list_item ); ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</footer>
	</div>
</article>
