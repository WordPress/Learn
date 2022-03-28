<?php
/**
 * Template Name: Content Calendar
 *
 * @package WPBBP
 */

namespace WordPressdotorg\Theme;

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main">

		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
		</div>
		<hr>

		<div id="main-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div id="content-calendar" class="type-page">
					<div class="entry-content">
						<h2 aria-hidden="true"><?php _e( 'Scheduled Content', 'wporg-learn' ); ?></h2>
						<?php

						$args = array(
							'post_type' 		=> array( 'wporg_workshop', 'lesson-plan', 'course' ),
							'post_status' 		=> array( 'future' ),
							'orderby' 			=> 'date modified title',
							'order'				=> 'ASC',
							'posts_per_page'	=> -1
						);

						$scheduled_content = get_posts( $args );

						if( $scheduled_content ) {
							?>
							<table>
								<thead>
									<tr>
										<th scope="col">
											<span class="screen-reader-text"><?php _e( 'Title', 'wporg-learn' ); ?></span>
											<span aria-hidden="true"><?php _e( 'Title', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span class="screen-reader-text"><?php _e( 'Publish Date', 'wporg-learn' ); ?></span>
											<span aria-hidden="true"><?php _e( 'Publish Date', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span class="screen-reader-text"><?php _e( 'Type', 'wporg-learn' ); ?></span>
											<span aria-hidden="true"><?php _e( 'Type', 'wporg-learn' ); ?></span>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach( $scheduled_content as $post ) { ?>
									<tr>
										<td><?php echo esc_html( $post->post_title ); ?></td>
										<td><?php echo wp_date( get_option( 'date_format' ), strtotime( $post->post_date ) ); ?></td>
										<td><?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?></td>
									</tr>
									<?php
									} ?>
								</tbody>
							</table>
						<?php } else { ?>
							<p><em><?php _e( 'No content scheduled', 'wporg-learn' ); ?></em></p>
						<?php }
						?>

						<h2 aria-hidden="true"><?php _e( 'Drafts in Progress', 'wporg-learn' ); ?></h2>
						<?php

						$args = array(
							'post_type' 		=> array( 'wporg_workshop', 'lesson-plan', 'course' ),
							'post_status' 		=> array( 'draft' ),
							'orderby' 			=> 'date modified title',
							'order'				=> 'ASC',
							'posts_per_page'	=> -1
						);

						$drafted_content = get_posts( $args );

						if( $drafted_content ) {
							?>
							<table>
								<thead>
									<tr>
										<th scope="col">
											<span class="screen-reader-text"><?php _e( 'Title', 'wporg-learn' ); ?></span>
											<span aria-hidden="true"><?php _e( 'Title', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span class="screen-reader-text"><?php _e( 'Type', 'wporg-learn' ); ?></span>
											<span aria-hidden="true"><?php _e( 'Type', 'wporg-learn' ); ?></span>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach( $drafted_content as $post ) { ?>
									<tr>
										<td><?php echo esc_html( $post->post_title ); ?></td>
										<td><?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?></td>
									</tr>
									<?php
									} ?>
								</tbody>
							</table>
						<?php } else { ?>
							<p><em><?php _e( 'No drafts in progress', 'wporg-learn' ); ?></em></p>
						<?php }
						?>
					</div>
				</div>

				<?php
			endwhile; // End of the loop.
			?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
