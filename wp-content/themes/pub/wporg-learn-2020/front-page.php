<?php

/**
 * The front page of the site.
 *
 * @package WPBBP
 */

get_header(); ?>

	<main id="main" class="site-main home-page">
		<section class="quick-intro">
			<div class="shapes">
				<a class="parallelogram workshops" href="/tutorials/">
					<p class="dashicons-before dashicons-desktop">
						<strong><?php esc_html_e( 'Tutorials', 'wporg-learn' ); ?></strong>
						<?php
						esc_html_e( 'Tutorials are a great way to get hands-on with WordPress. Here are some tutorials for you to level up your WordPress skills.', 'wporg-learn' );
						?>
						<u><?php esc_html_e( 'Browse Tutorials', 'wporg-learn' ); ?></u>
					</p>
				</a>
				<a class="parallelogram lesson-plans" href="/lesson-plans/">
					<p class="dashicons-before dashicons-clipboard">
						<strong><?php esc_html_e( 'Lesson Plans', 'wporg-learn' ); ?></strong>
						<?php
						esc_html_e( 'Are you sharing WordPress with others? These lesson plans are designed to guide and inspire you to deliver great content.', 'wporg-learn' );
						?>
						<u><?php esc_html_e( 'Access Lesson Plans', 'wporg-learn' ); ?></u>
					</p>
				</a>
			</div>
		</section>

		<section>
			<div class="section-heading row align-middle between gutters">
				<h2 class="h4 section-heading_title col-8"><?php esc_html_e( 'Recent Courses', 'wporg-learn' ); ?></h2>
				<div class="section-heading_links col-4 row">
					<?php if ( is_user_logged_in() ) : ?>
						<a class="section-heading_link" href="/my-courses/"><?php esc_html_e( 'My Courses', 'wporg-learn' ); ?></a>
					<?php endif; ?>
					<a class="section-heading_link" href="/courses/"><?php esc_html_e( 'View All Courses', 'wporg-learn' ); ?></a>
				</div>
				<p class="section-heading_description">
					<em><?php esc_html_e( 'Multimedia courses setting you up to become a better WordPress developer, designer, user, and contributor.', 'wporg-learn' ); ?></em>
				</p>
			</div>

			<?php
			$args = array(
				'query' => wporg_get_archive_query(
					'course',
					array(
						'posts_per_page' => 2,
						'meta_query' =>
							array(
								array(
									'key'   => '_course_featured',
									'value' => 'featured',
								),
							),
					),
				),
			);
			get_template_part( 'template-parts/component', 'course-grid', $args );
			?>
		</section>

		<hr>

		<section>
			<div class="section-heading row align-middle between gutters">
				<h2 class="h4 section-heading_title col-8">
					<?php esc_html_e( 'Recent Tutorials', 'wporg-learn' ); ?>
				</h2>
				<div class="section-heading_links col-4 row">
					<a class="section-heading_link" href="/tutorials/">
						<?php esc_html_e( 'View All Tutorials', 'wporg-learn' ); ?>
					</a>
				</div>
				<p class="section-heading_description">
					<em><?php esc_html_e( 'Instructional videos for all skillsets to help you level up your WordPress expertise.', 'wporg-learn' ); ?></em>
				</p>
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
				<div class="section-heading row align-middle between gutters">
					<h2 class="h4 section-heading_title col-8">
						<?php esc_html_e( 'Upcoming Online Workshops', 'wporg-learn' ); ?>
					</h2>
					<div class="section-heading_links col-4 row">
						<a class="section-heading_link" href="/online-workshops/">
							<?php esc_html_e( 'View All Online Workshops', 'wporg-learn' ); ?>
						</a>
					</div>
					<p class="section-heading_description">
						<em><?php esc_html_e( 'Live sessions where you can learn alongside other WordPress enthusiasts from around the world.', 'wporg-learn' ); ?></em>
					</p>
				</div>

				<ul class="discussion-event-list">
					<?php foreach ( $discussion_events as $event ) : ?>
						<?php get_template_part( 'template-parts/component', 'discussion-event-short-item', $event ); ?>
					<?php endforeach; ?>
				</ul>
				<p>
					<?php
					printf(
						wp_kses_post( __( 'Want to facilitate an online workshop? <a href="%s">Apply to become a facilitator</a>.', 'wporg-learn' ) ),
						'https://learn.wordpress.org/online-workshops/'
					);
					?>
				</p>
			</section>

			<hr>
		<?php endif; ?>

		<section class="about-training">
			<div class="getin">
				<div class="graphic"><span class="dashicons dashicons-welcome-learn-more"></span></div>
				<div>
					<h2 class="h3"><?php esc_html_e( 'Get Involved', 'wporg-learn' ); ?></h2>
					<p><?php esc_html_e( 'Want to get involved in creating the content for Learn WordPress?', 'wporg-learn' ); ?></p>
					<a href="//learn.wordpress.org/contribute"><?php esc_html_e( 'Learn how to contribute', 'wporg-learn' ); ?></a>
				</div>
			</div>
		</section>

		<hr>

		<?php get_template_part( 'template-parts/component', 'submit-idea-cta', array( 'icon' => 'lightbulb' ) ); ?>
	</main>

<?php get_footer();
