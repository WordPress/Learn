<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

$search_query = sprintf(
	/* translators: Search query. */
	esc_html__( 'Search Results for: %s', 'wporg-learn' ),
	get_search_query()
);

get_header(); ?>

	<main id="main" class="site-main type-page" role="main">
		<?php if ( have_posts() ) : ?>
			<section>
				<div class="row align-middle between section-heading section-heading--with-space">
					<h1 class="section-heading_title h2"><?php echo esc_html( $search_query ); ?></h1>
				</div>
				<hr>
				<div class="search-grid">
					<?php
					// Start the loop.
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/component', 'post-card' );
					endwhile;
					// If no content, include the "No posts found" template.
				else :
					get_template_part( 'template-parts/content', 'none' );

				endif;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		</section>
	</main><!-- .site-main -->

<?php
get_footer();
