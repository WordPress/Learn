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
						<strong><?php _e( 'Workshops', 'wporg-forums' ); ?></strong>
						<?php
						_e( 'Workshops are a great way to get hands-on with WordPress. Here are some workshops for you to level up your WordPress skills.', 'wporg-learn' );
						?>
						<u><?php _e( 'Browse Workshops' ); ?></u>
					</p>
				</a>
				<a class="parallelogram lesson-plans dashicons-before dashicons-clipboard" href="/lesson-plans/">
					<p>
						<strong><?php _e( 'Lesson Plans', 'wporg-forums' ); ?></strong>
						<?php
							_e( 'Are you teaching WordPress to others? These lesson plans are designed to guide and inspire you to deliver great content.', 'wporg-learn' );
						?>
						<u><?php _e( 'See the Lesson Plans' ); ?></u>
					</p>
				</a>
			</div>
		</section>

		<section class="about-training">
			<div class="getin">
				<div class="graphic"><span class="dashicons dashicons-welcome-learn-more"></span></div>
				<div>
					<h2 class="h3"><?php _e( 'Get Involved' ); ?></h2>
					<p><?php _e( 'Want to grow WordPress? As part of Training Team you could help others learn WordPress accross the world.' ); ?></p>
					<a href="https://make.wordpress.org/training/handbook/"><?php _e( 'Learn About The Training Team' ); ?></a>
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
					'posts_per_page' => '3',
				);
				set_query_var( 'video-grid-options', $args );
				get_template_part( 'template-parts/component', 'video-grid' );
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
				<h3><?php esc_html_e( 'Support Forums', 'wporg-forums' ); ?></h3>

				<p>
					<span>
						<?php esc_html_e( 'Can\'t find what you\'re looking for? Find out if others share your experience.', 'wporg-forums' ); ?>
					</span>

					<br>

					<a href="<?php echo esc_url( site_url( '/forums/' ) ); ?>"><?php esc_html_e( 'Check out our support forums', 'wporg-forums' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

	</main>

<?php
get_footer();
