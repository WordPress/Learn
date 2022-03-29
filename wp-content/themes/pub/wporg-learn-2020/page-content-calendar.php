<?php
/**
 * Template Name: Upcoming Content
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
						<h2 aria-hidden="true"><?php esc_html_e( 'Scheduled Content', 'wporg-learn' ); ?></h2>
						<?php

						$args = array(
							'post_type'      => array( 'wporg_workshop', 'lesson-plan', 'course' ),
							'post_status'    => array( 'future' ),
							'orderby'        => 'date modified title',
							'order'          => 'ASC',
							'posts_per_page' => -1,
						);

						$scheduled_content = get_posts( $args );

						if ( $scheduled_content ) {
							?>
							<table>
								<thead>
									<tr>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Title', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Type', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Publish Date', 'wporg-learn' ); ?></span>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $scheduled_content as $scheduled_post ) { ?>
										<tr>
											<td><?php echo esc_html( $scheduled_post->post_title ); ?></td>
											<td><?php echo esc_html( get_post_type_object( $scheduled_post->post_type )->labels->singular_name ); ?></td>
											<td><?php echo esc_html( wp_date( 'j F Y @ G:i', strtotime( $scheduled_post->post_date ) ) ); ?> <?php esc_html_e( 'UTC', 'wporg-learn' ); ?></td>
										</tr>
										<?php
									} ?>
								</tbody>
							</table>
						<?php } else { ?>
							<p><em><?php esc_html_e( 'No content scheduled', 'wporg-learn' ); ?></em></p>
						<?php }
						?>

						<h2 aria-hidden="true"><?php esc_html_e( 'Content in Progress', 'wporg-learn' ); ?></h2>
						<?php

						$statuses = array(
							'draft',
							'needs-vetting',
							'approved-for-video',
							'more-info-requested',
							'needs-grammar-review',
							'needs-seo-review',
							'needs-tech-review',
							'needs-video-review',
						);

						$args = array(
							'post_type'      => array( 'wporg_workshop', 'lesson-plan', 'course' ),
							'post_status'    => $statuses,
							'orderby'        => 'modified title',
							'order'          => 'DESC',
							'posts_per_page' => -1,
							'post__not_in'   => array( 377, 378 ),
						);

						$drafted_content = get_posts( $args );

						if ( $drafted_content ) {
							?>
							<table>
								<thead>
									<tr>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Title', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Type', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Status', 'wporg-learn' ); ?></span>
										</th>
										<th scope="col">
											<span aria-hidden="true"><?php esc_html_e( 'Last Updated', 'wporg-learn' ); ?></span>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $drafted_content as $drafted_post ) {
										?>
										<tr>
											<td><?php echo esc_html( $drafted_post->post_title ); ?></td>
											<td><?php echo esc_html( get_post_type_object( $drafted_post->post_type )->labels->singular_name ); ?></td>
											<td><?php echo esc_html( get_post_status_object( $drafted_post->post_status )->label ); ?></td>
											<td><?php echo esc_html( wp_date( 'j F Y @ G:i', strtotime( $drafted_post->post_modified ) ) ); ?> <?php esc_html_e( 'UTC', 'wporg-learn' ); ?></td>
										</tr>
										<?php
									} ?>
								</tbody>
							</table>
						<?php } else { ?>
							<p><em><?php esc_html_e( 'No drafts in progress', 'wporg-learn' ); ?></em></p>
						<?php }
						?>
					</div>
				</div>

				<?php

				get_template_part( 'template-parts/content', 'page' );
			endwhile; // End of the loop.
			?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
