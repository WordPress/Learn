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

/** @var WP_Post $post */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">

	<section>
		<div class="section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
		</div>
		<hr>
		<?php if ( is_post_type_archive( 'wporg_idea' ) ) : ?>
			<div class="section-intro">
				<div class="row between gutters">
					<p class="col-8"><?php esc_html_e( 'Content ideas can be submitted by any learner. Browse the ideas submitted by others, vote on the ones you like, and suggest your own topics for future content development.', 'wporg-learn' ); ?></p>
					<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
				</div>
			</div>
			<hr>
		<?php endif; ?>

		<div class="lp-archive-items row gutters between">
			<div class="card-grid col-8">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) :
						the_post();
						get_template_part(
							'template-parts/component',
							'card',
							wporg_learn_get_card_template_args( get_the_ID() )
						);
					endwhile; ?>
				<?php else : ?>
					<p class="not-found">
						<?php echo esc_html( get_post_type_object( 'wporg_idea' )->labels->not_found ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="col-4">
				<?php get_template_part( 'template-parts/component', 'ideas-form' ); ?>
			</div>
		</div>

		<?php the_posts_pagination(); ?>

	</section>

	<hr>

</main>

<?php get_footer();
