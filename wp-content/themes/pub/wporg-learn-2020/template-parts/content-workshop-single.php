<?php

global $wp_embed;

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php the_title(); ?></h1>
		</div>

		<hr>

		<div class="workshop-page">
			<?php if ( $post->video_url ) : ?>
				<figure class="workshop-page_video">
					<?php echo $wp_embed->autoembed( $post->video_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</figure>
			<?php endif; ?>

			<div class="wp-block-columns workshop-page_content">
				<div class="wp-block-column" style="flex-basis:66.66%">
					<?php the_content(); ?>
				</div>

				<div class="wp-block-column workshop-page_sidebar" style="flex-basis:33.333%">
					<div>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo render_block( array(
							'blockName' => 'wporg-learn/workshop-details',
							'attrs'     => array(),
						) );
						?>
					</div>

					<div class="wp-block-button is-style-secondary-full-width">
						<a
							class="wp-block-button__link"
							href="https://www.meetup.com/learn-wordpress-discussions/events/"
							style="border-radius:5px"
						>
							<?php esc_html_e( 'Join a Group Discussion', 'wporg-learn' ); ?>
						</a>
					</div>

					<p class="terms">
						<?php printf(
							wp_kses_data( __( 'You must agree to our <a href="%s">Code of Conduct</a> in order to participate.', 'wporg-learn' ) ),
							esc_url( get_permalink( get_page_by_path( 'code-of-conduct' ) ) )
						); ?>
					</p>

				</div> <!-- end sidebar column -->
			</div> <!-- end columns -->

			<hr class="wp-block-separator">

			<?php if ( is_object_in_term( get_the_ID(), 'wporg_workshop_series' ) ) : ?>
				<?php get_template_part( 'template-parts/component', 'series-navigation' ); ?>
				<hr class="wp-block-separator" />
			<?php endif; ?>

			<?php foreach ( wporg_get_workshop_presenters() as $presenter ) : ?>
				<section class="row workshop-page_section"">
					<div class="col-4">
						<?php
						get_template_part(
							'template-parts/component',
							'workshop-presenter',
							array(
								'presenter' => $presenter,
							)
						);
						?>
					</div>

					<div class="col-8 workshop-page_biography">
						<?php echo wp_kses_post( wpautop( wporg_get_workshop_presenter_bio( $presenter ) ) ); ?>
					</div>
				</section>
			<?php endforeach; ?>

		</div> <!-- end workshop-page -->
	</section>
</article>
