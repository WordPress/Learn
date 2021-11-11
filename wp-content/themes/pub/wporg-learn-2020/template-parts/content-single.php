<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<section>
		<header class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php the_title(); ?></h1>
		</header>

		<div class="lp-content">
			<div class="lp-content-inner github-markdown">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wporg-learn' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>

			<?php
			/**
			 * Read in passed context and render sidebar.
			 */
			switch ( get_post_type() ) {
				case 'lesson-plan':
					get_sidebar( 'lesson-plan' );
					break;
				case 'course':
					get_sidebar( 'course' );
					break;
			}
			?>
		</div>
	</section>

	<?php if ( is_object_in_term( get_the_ID(), 'wporg_lesson_plan_series' ) ) : ?>
		<hr class="wp-block-separator" />
		<?php get_template_part( 'template-parts/component', 'series-navigation' ); ?>
	<?php endif; ?>
</article><!-- #post-## -->
