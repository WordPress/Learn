<?php

/**
 * The front page of the site.
 *
 * @package WPBBP
 */

get_header(); ?>

	<main id="main" class="site-main home-page" role="main">
		<section class="quick-intro">
			<div class="shapes">
				<a class="parallelogram workshops" href="/workshops/">
					<p class="dashicons-before dashicons-desktop">
						<strong><?php esc_html_e( 'Workshops', 'wporg-learn' ); ?></strong>
						<?php
						esc_html_e( 'Workshops are a great way to get hands-on with WordPress. Here are some workshops for you to level up your WordPress skills.', 'wporg-learn' );
						?>
						<u><?php esc_html_e( 'Browse Workshops', 'wporg-learn' ); ?></u>
					</p>
				</a>
				<a class="parallelogram lesson-plans" href="/lesson-plans/">
					<p class="dashicons-before dashicons-clipboard">
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
				'query' => wporg_get_archive_query(
					'wporg_workshop',
					array(
						'posts_per_page' => 6,
					)
				),
			);
			get_template_part( 'template-parts/component', 'video-grid', $args );
			?>
		</section>

		<hr>

		<?php $discussion_events = \WPOrg_Learn\Events\get_discussion_events(); ?>
		<?php if ( ! empty( $discussion_events ) ) : ?>
			<section class="wporg-learn-workshop-discussion-events">
				<div class="row align-middle between section-heading">
					<h2 class="h4 section-heading_title">
						<?php esc_html_e( 'Upcoming Discussion Groups', 'wporg-learn' ); ?>
					</h2>
					<a class="section-heading_link" href="https://www.meetup.com/learn-wordpress-discussions/">
						<?php esc_html_e( 'View All »', 'wporg-learn' ); ?>
					</a>
				</div>
				<ul class="discussion-event-list">
					<?php foreach ( $discussion_events as $event ) : ?>
						<?php get_template_part( 'template-parts/component', 'discussion-event-short-item', $event ); ?>
					<?php endforeach; ?>
				</ul>
			</section>

			<hr>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/component', 'submit-idea-cta', array( 'icon' => 'lightbulb' ) ); ?>
	</main>

<?php get_footer();
