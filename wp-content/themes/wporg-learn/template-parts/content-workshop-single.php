<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<main class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h2 class="section-heading_title"><?php the_title(); ?></h2>
		</div>
		<hr>

		<?php the_content(); ?>
	</section>
</article>