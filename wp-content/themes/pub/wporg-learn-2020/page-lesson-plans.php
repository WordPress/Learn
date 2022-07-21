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

			<div class="row align-middle between section-plan_description section-heading--with-space">
				<div class="section-plan_text">
					<?php the_content(); ?>
				</div>
				<?php
				set_query_var('post_type', 'lesson-plan');
				get_template_part( 'template-parts/component', 'archive-search' );
				?>
			</div>

			<hr>
			<?php
			$categories = get_terms( 'wporg_lesson_category', array(
				'hide_empty' => false,
				'orderby' => 'id',
			) );
			?>
			<div class="row lesson-plan-category">
				<div class="card-grid card-grid_4">
					<h2 class="h4 lesson-plan-category-header"><?php echo esc_html__( 'Topic', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-category-description"><?php echo esc_html__( 'Browse lesson plans by their high-level topic.', 'wporg-learn' ); ?></div>
					<?php foreach ( $categories as $category) : ?>
					<div class="card">
						<div class="icon">
							<?php
							$category_icon = get_term_meta( $category->term_id, 'dashicon-class', true );
							?>
							<a href="<?php echo get_term_link( $category ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $category_icon ); ?>"></span></a>
						</div>
						<p class="category-title"><a href="<?php echo get_term_link( $category ); ?>"><?php echo esc_html( $category->name ); ?></a></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
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
