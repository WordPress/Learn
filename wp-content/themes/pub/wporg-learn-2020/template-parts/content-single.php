<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WPBBP
 */


$slides_url = wporg_get_slides_url();
$download_url = wporg_get_download_slides_url();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">	
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title h3">', '</h1>' ); ?>
		</header><!-- .entry-header -->
		<div class="lp-content">
			<div class="lp-content-inner github-markdown">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wporg-forums' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
			<aside class="lp-sidebar">
				<div class="lp-details">
					<ul>
						<?php 
							foreach( wporg_get_custom_taxonomies( get_the_ID() ) as $detail ) {
								if( !empty( $detail[ 'values' ] ) ) {
									include( locate_template( 'template-parts/component-taxonomy-item.php' ) ); 
								}			
							}
						?>
					</ul>

					<ul class="lp-links">

					<?php if( $slides_url ) : ?>
						<li>
							<a href="<?php echo $slides_url; ?>" target="_blank"><span class="dashicons dashicons-admin-page"></span> <?php _e( 'View Lesson Plan Slides' ); ?></a>
						</li>
					<?php endif; ?>

					<?php if( $download_url ) : ?>
						<li>
							<a href="<?php echo $download_url; ?>"><span class="dashicons dashicons-download"></span> <?php _e( 'Download Lesson Slides' ); ?></a>
						</li>
					<?php endif; ?>
			
						<!-- <li>
							<a href="#" target="_blank"><span class="dashicons dashicons-admin-post"></span> <?php _e( 'Print Lesson Plan' ); ?></a>
						</li> -->
					</ul>

					<div class="lp-suggestion">
						<h4 class="lp-suggestion_title"><?php _e( 'Suggestions' ); ?></h4>
						<p><?php _e( 'Found a typo, grammar error,or outdated screenshot?' ); ?></p>
						<p><?php _e( 'Used this lesson plan in your event and have some suggestions?' ); ?></p>
						<a href="<?php echo esc_url( site_url( '/submit-an-idea/' ) ); ?>"><?php _e( 'Let us know!' ); ?></a>
					</div>
				</div>
			</aside>
			</div>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
