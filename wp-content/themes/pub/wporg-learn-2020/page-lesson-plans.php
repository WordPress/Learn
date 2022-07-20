<?php
/**
 * Template Name: Lesson Plans
 *
 * @package WPBBP
 */

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

	<main id="main" class="site-main page-full-width">
		<section>
			<div class="row align-middle between section-heading section-heading--with-space">
				<?php the_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
				<a href="<?php echo get_post_type_archive_link( 'lesson-plan' ); ?>" class="button button-large button-secondary">
					<?php esc_html_e( 'Browse all lesson plans', 'wporg-learn' ); ?>
				</a>
			</div>

			<hr>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div id="lesson-plans" class="lp-list">
				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="lp-item">
					<div class="lp-item-wrap">
						<h2><a href="#">Lesson Plan Title</a></h2>
						<p class="lp-excerpt">The training team creates downloadable lesson plans and related materials for instructors to use in a live workshop environment. We are welcome you to join Training Team.</p>
						<div class="lp-details">
							<div class="left-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-clock"></span>
										Length: <strong>1 Hour</strong>
									</li>
									<li>
										<span class="dashicons dashicons-admin-users"></span>
										Audience: <strong>Developers</strong>
									</li>
									<li>
										<span class="dashicons dashicons-dashboard"></span>
										Level: <strong>Beginning</strong>
									</li>
								</ul>
							</div>
							<div class="right-items items">
								<ul>
									<li>
										<span class="dashicons dashicons-welcome-learn-more"></span>
										Type of Instruction: <strong>Presentation, Demostration</strong>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		endwhile; // End of the loop.
		?>
	</section>

	</main><!-- #main -->

<?php
get_footer();
