<?php

/**
 * The front page of the site.
 *
 * @package WPBBP
 */

get_header();?>

	<main id="main" class="site-main home-page" role="main">
		<section class="quick-intro">
			<div class="shapes">
				<a class="parallelogram workshop-ideas dashicons-before dashicons-slides" href="/workshops/">
					<p>
						<strong><?php esc_html_e( 'Workshops', 'wporg-learn' ); ?></strong>
						<?php
						esc_html_e( 'Workshops are a great way to get hands-on with WordPress. Here are some workshops for you to level up your WordPress skills.', 'wporg-learn' );
						?>
						<u><?php esc_html_e( 'Browse Workshops', 'wporg-learn' ); ?></u>
					</p>
				</a>
				<a class="parallelogram lesson-plans dashicons-before dashicons-clipboard" href="/lesson-plans/">
					<p>
						<strong><?php esc_html_e( 'Lesson Plans', 'wporg-learn' ); ?></strong>
						<?php
						esc_html_e( 'Are you teaching WordPress to others? These lesson plans are designed to guide and inspire you to deliver great content.', 'wporg-learn' );
						?>
						<u><?php esc_html_e( 'See the Lesson Plans', 'wporg-learn' ); ?></u>
					</p>
				</a>
			</div>
		</section>

		<section class="about-training">
			<div class="getin">
				<div class="graphic"><span class="dashicons dashicons-welcome-learn-more"></span></div>
				<div>
					<h2 class="h3"><?php esc_html_e( 'Get Involved', 'wporg-learn' ); ?></h2>
					<p><?php esc_html_e( 'Want to grow WordPress? As part of Training Team you could help others learn WordPress accross the world.', 'wporg-learn' ); ?></p>
					<a href="https://make.wordpress.org/training/handbook/"><?php esc_html_e( 'Learn About The Training Team', 'wporg-learn' ); ?></a>
				</div>
			</div>
		</section>

		<hr>
		<section>
			<div class="row align-middle between section-heading">
				<h2 class="h4 section-heading_title"><?php esc_html_e( 'Recent Workshops', 'wporg-learn' ); ?></h2>
				<a class="section-heading_link" href="/workshops"><?php esc_html_e( 'View All »', 'wporg-learn' ); ?></a>
			</div>

			<?php
			$args = array(
				'query' => wporg_get_workshops_query( array(
					'posts_per_page' => 6,
				) ),
			);
			get_template_part( 'template-parts/component', 'video-grid', $args );
			?>
		</section>
		<hr>

		<?php get_template_part( 'template-parts/component', 'submit-idea-cta', array( 'icon' => 'lightbulb' ) ); ?>

		<?php if ( ! is_active_sidebar( 'front-page-blocks' ) ) : ?>
			<?php //get_template_part( 'template-parts/bbpress', 'front' ); ?>
		<?php else : ?>
			<div class="three-up helphub-front-page">
				<?php dynamic_sidebar( 'front-page-blocks' ); ?>
			</div>

			<hr>

			<div id="helphub-forum-link" class="text-center">
				<h3><?php esc_html_e( 'Support Forums', 'wporg-learn' ); ?></h3>

				<p>
					<span>
						<?php esc_html_e( 'Can\'t find what you\'re looking for? Find out if others share your experience.', 'wporg-learn' ); ?>
					</span>

					<br>

					<a href="<?php echo esc_url( site_url( '/forums/' ) ); ?>"><?php esc_html_e( 'Check out our support forums', 'wporg-learn' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

	</main>

<?php
get_footer();
