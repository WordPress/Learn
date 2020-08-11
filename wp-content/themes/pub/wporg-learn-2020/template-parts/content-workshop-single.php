<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<main class="site-main">
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h2 class="section-heading_title"><?php the_title(); ?></h2>
		</div>
		<hr>
		<div class="workshop-page">
			<?php the_content(); ?>
			<?php foreach ( wporg_get_workshop_authors() as $author ) {
				$author = get_user_by( 'login', $author );
			
				if( $author ) {
					$test = wporg_set_workshop_author_query_var( $author );
				?>

				<section class="row">
					<div class="col-4">
						<?php echo get_template_part( 'template-parts/component', 'author' ); ?>
					</div>
					<p class="col-8"><?php echo $author->description; ?></p>
				</section>
			<?php 
				}
			}
			?>
		</div>
	</section>
</article>