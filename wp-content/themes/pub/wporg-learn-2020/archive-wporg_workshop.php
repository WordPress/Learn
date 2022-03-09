<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

use WP_Post;

global $wp_query;
$is_filtered = $wp_query->get( 'wporg_archive_filters' );

/** @var WP_Post $post */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">

	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
			<?php if ( is_tax( 'wporg_workshop_series' ) && have_posts() ) :
				$series_term = wporg_learn_series_get_term( $post );
				?>
				<div class="section-heading_description col-12">
					<?php echo wp_kses_post( wpautop( term_description( $series_term->term_id ) ) ); ?>
				</div>
			<?php endif; ?>
		</div>
		<hr>
		<?php if ( is_post_type_archive( 'wporg_workshop' ) ) : ?>
			<?php get_template_part( 'template-parts/component', 'workshop-filters' ); ?>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php // Only show the featured workshop on the first page of post type archives.
			if ( is_post_type_archive() && get_query_var( 'paged' ) < 2 && ! $is_filtered ) : ?>
				<?php get_template_part( 'template-parts/component', 'featured-workshop' ); ?>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/component', 'video-grid' ); ?>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="not-found">
				<?php echo esc_html( get_post_type_object( 'wporg_workshop' )->labels->not_found ); ?>
			</p>
		<?php endif; ?>
	</section>
	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
</main>

<?php get_footer();
