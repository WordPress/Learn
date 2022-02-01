<?php
/**
 * Template part for displaying the featured workshop.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

$featured_workshop = wporg_get_featured_workshops();
$featured_workshop = reset( $featured_workshop );
?>

<?php if ( $featured_workshop ) : ?>
	<div class="featured-workshop">
		<?php
		$post = $featured_workshop; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		?>
			<div aria-hidden="true" tabindex="-1" class="featured-workshop_video">
				<a href="<?php echo esc_url( get_the_permalink() ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wporg_get_post_thumbnail( $post, 'full' );
					?>
				</a>
			</div>
			<div class="featured-workshop_content">
				<a class="featured-workshop_title" href="<?php echo esc_url( get_the_permalink() ); ?>">
					<span aria-hidden="true"><?php the_title(); ?></span>
					<h3 class="screen-reader-text"><?php the_title(); ?></h3>
				</a>
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
								array(
									'presenter' => $presenters[0],
									'class' => 'workshop-presenter--is-centered',
								)
							);
						endif;
						?>
					</div>
				</div>
			</div>
		<?php wp_reset_postdata(); ?>
	</div>
<?php endif; ?>
