<?php

global $wp_embed;

$presenters = wporg_get_workshop_presenters();

$other_contributors = array_map(
	function( $other_contributor ) {
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			sprintf(
				'https://profiles.wordpress.org/%s/',
				esc_attr( $other_contributor->user_login )
			),
			esc_html( $other_contributor->display_name )
		);
	},
	wporg_get_workshop_other_contributors()
);
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

					<?php get_sidebar( 'workshop' ); ?>

				</div> <!-- end sidebar column -->
			</div> <!-- end columns -->

			<hr class="wp-block-separator">

			<?php if ( is_object_in_term( get_the_ID(), 'wporg_workshop_series' ) ) : ?>
				<?php get_template_part( 'template-parts/component', 'series-navigation' ); ?>
				<hr class="wp-block-separator" />
			<?php endif; ?>

			<?php if ( ! empty( $presenters ) ) : ?>
				<section class="row workshop-page_section">
					<h2><?php esc_html_e( 'Presenters', 'wporg_learn' ); ?></h2>
					<?php foreach ( $presenters as $presenter ) : ?>
						<div class="row col-12">
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
						</div>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $other_contributors ) ) : ?>
				<section class="row workshop-page_section">
					<h2><?php esc_html_e( 'Other Contributors', 'wporg_learn' ); ?></h2>
					<p class="col-12">
						<?php
						// translators: Used between list items, there is a space after the comma.
						echo wp_kses_post( implode( __( ', ', 'wporg-learn' ), $other_contributors ) );
						?>
					</p>
				</section>
			<?php endif; ?>
		</div> <!-- end workshop-page -->
	</section>
</article>
