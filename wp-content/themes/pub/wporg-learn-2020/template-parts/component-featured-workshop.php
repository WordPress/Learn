<?php
/**
 * Template part for displaying the featured workshop.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

use function WPOrg_Learn\Post_Meta\get_workshop_duration;

$featured_workshop = wporg_get_featured_workshops();
$featured_workshop = reset( $featured_workshop );
?>

<?php if ( $featured_workshop ) : ?>
	<div class="featured-workshop">
		<?php
		$post = $featured_workshop; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$duration = get_workshop_duration( $post, 'string' );
		?>
			<div class="featured-workshop_video">
				<a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php the_title(); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wporg_get_post_thumbnail( $post, 'full' );
					?>
				</a>
			</div>
			<div class="featured-workshop_content">
				<h2 class="featured-workshop_title">
					<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
				</h2>
				<?php if ( isset( $duration ) ) { ?>
					<p class="featured-workshop_content_duration">Length: <?php echo esc_html( $duration ); ?></p>
				<?php } ?>
				<div class="row">
					<div class="col-8">
						<?php the_excerpt(); ?>
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
