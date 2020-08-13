<?php
/**
 * Template part for displaying the featured workshop.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$featured_workshop = wporg_get_workshops( array( 'posts_per_page' => 1 ) );
?>

<div class="featured-workshop">
	<?php while ( $featured_workshop->have_posts() ) : $featured_workshop->the_post(); ?>
		<div class="featured-workshop_video"><?php echo wporg_get_post_thumbnail( $post, 'full' ); ?></div>
		<div class="featured-workshop_content">
			<a class="featured-workshop_title" href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo the_title() ?></a>
			<div class="row">
				<div class="col-8">
					<p><?php the_excerpt(); ?></p>
				</div>
				<div class="col-4 featured-workshop_content_author">
					<?php
						$presenters = wporg_get_workshop_presenters();

						// We'll only display the first author in this view
						if ( isset( $presenters[0] ) ) :
							get_template_part( 'template-parts/component', 'workshop-presenter',
								array( 'presenter' => $presenters[0], 'class' => 'workshop-presenter--is-centered' )
							);
						endif;
					?>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
</div>
