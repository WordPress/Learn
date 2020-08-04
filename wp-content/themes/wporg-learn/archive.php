<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;


/**
 * Gets the url for the next page
 * 
 * @return string
 */
function get_paging_url() {
	$non_existing_page_num = 999999999;

	// We will send a dummy number into the get_pagenum_link to get a url and replace that with a placeholder
	$url_with_placeholder = str_replace( $non_existing_page_num, '%#%', esc_url( get_pagenum_link( $non_existing_page_num ) ) );
	$default_cat = wporg_get_default_cat();
	
	// Because of routing, we want to inject the first category if they are in the root.
	return preg_replace( '/(workshops|lesson-plans)\/page/', '$1/' . $default_cat->slug . '/page' , $url_with_placeholder );
}

$paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;

$args = array(
	'posts_per_page' => get_option( 'posts_per_page' ),
	'post_type' => get_post_type(),
	'category_name' => wporg_get_cat_or_default_slug(),
	'paged' => $paged,
);

$category_posts = new \WP_Query( $args );

get_header();
?>

<?php get_template_part( 'template-parts/component', 'directory-nav' ); ?>

<main id="main" class="site-main page-full-width" role="main">
	<?php get_template_part( 'template-parts/component', 'filters' ); ?>

	<?php if ( $category_posts->have_posts() ) : ?>

		<div id="lesson-plans" class="lp-list">
		
			<?php while ( $category_posts->have_posts() ) :
					$category_posts->the_post();
					get_template_part( 'template-parts/content', 'archive' );
				endwhile; 
			?>
			
		</div>
		
		<?php echo paginate_links( array(
				'base' => get_paging_url(),
				'format' => '?page=%#%',
				'current' => max( 1, get_query_var('page') ),
				'total' => $category_posts->max_num_pages
			) ); ?>

	<?php else : ?>
		<div class="lp-empty"><?php echo _e("We were unable to find any matches." , 'wporg-learn'); ?></div>
	<?php endif; ?>

</main><!-- #main -->

<?php if ( $category_posts->have_posts() ) : ?>
	<?php wporg_submit_idea_cta(); ?>
<?php endif; ?>

<?php
get_footer();
