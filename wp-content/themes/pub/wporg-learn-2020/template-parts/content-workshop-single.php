<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php the_title(); ?></h1>
		</div>
		<hr>
		<div class="workshop-page">
			<?php the_content(); ?>
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
		</div>
	</section>
</article>
