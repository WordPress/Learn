<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<main class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h2 class="section-heading_title"><?php the_title(); ?></h2>
		</div>
		<hr>
		<div class="workshop-page">
			<?php the_content(); ?>

			<section class="row">
				<div class="col-4">
					<?php get_template_part( 'template-parts/component', 'author' ); ?>
				</div>
				<p class="col-8"><?php echo get_the_author_meta( 'description' ); ?></p>
			</section>
		</div>
	</section>
</article>