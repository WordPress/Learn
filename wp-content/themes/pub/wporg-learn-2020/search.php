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
		<div class="clearfix">
			<div class="bbp-breadcrumb">
				<p>
					<a href="<?php echo esc_url( home_url() ); ?>" class="bbp-breadcrumb-home"><?php esc_html_e( 'Learn Home', 'wporg-learn' ); ?></a>
					<span class="bbp-breadcrumb-sep">»</span>
					<span class="bbp-breadcrumb-current"><?php echo esc_html( $search_query ); ?></span>
				</p>
			</div>
		</div>

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="h3"><?php echo esc_html( $search_query ); ?></h1>
			</header><!-- .page-header -->
			<div class="search-grid">
			<?php
			// Start the loop.
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/component', 'post-card' );
			endwhile;

			the_posts_pagination();

			// If no content, include the "No posts found" template.
		else :
			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>
		</div>
	</main><!-- .site-main -->

<?php
get_footer();
