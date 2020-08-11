<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php the_title(); ?></h1>
		</div>
		<hr>
		<div class="workshop-page">
			<?php the_content(); ?>
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
					<p class="col-8"><?php echo esc_html( $presenter->description ); ?></p>
				</section>
			<?php endforeach; ?>
		</div>
	</section>
</article>
