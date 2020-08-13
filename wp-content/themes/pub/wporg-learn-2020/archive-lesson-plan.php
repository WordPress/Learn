<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

get_header();
?>

	<main id="main" class="site-main page-full-width" role="main">
		<?php get_template_part( 'template-parts/component', 'lesson-filters' ); ?>

		<?php if ( have_posts() ) : ?>
			<div id="lesson-plans" class="lp-list">
				<?php while ( have_posts() ) :
					the_post(); ?>
					<?php get_template_part( 'template-parts/content', 'archive' ); ?>
				<?php endwhile; ?>
			</div>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="lp-empty">
				<?php echo esc_html_e( 'We were unable to find any matches.', 'wporg-learn' ); ?>
			</div>
		<?php endif; ?>

	</main><!-- #main -->

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>

<?php
get_footer();
