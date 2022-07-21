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
			$audiences = get_terms( 'audience', array(
				'hide_empty' => false,
				'orderby' => 'id',
			) );
			?>
			<div class="row lesson-plan-category">
				<div class="card-grid card-grid_4">
					<h2 class="h4 lesson-plan-category-header"><?php echo esc_html__( 'Audience', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-category-description"><?php echo esc_html__( 'Browse lesson plans by the audience they\'re intended for.', 'wporg-learn' ); ?></div>
					<?php foreach ( $audiences as $audience) : ?>
					<div class="card">
						<div class="icon">
							<?php
							$audience_icon = get_term_meta( $audience->term_id, 'dashicon-class', true );
							?>
							<a href="<?php echo get_term_link( $audience ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $audience_icon ); ?>"></span></a>
						</div>
						<p class="category-title"><a href="<?php echo get_term_link( $audience ); ?>"><?php echo esc_html( $audience->name ); ?></a></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="row lesson-plan-two-col between">
				<div class="lesson-plan-level">
					<h2 class="h4 lesson-plan-level-header"><?php echo esc_html__( 'Level', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-level-description"><?php echo esc_html__( 'What experience partipants need.', 'wporg-learn' ); ?></div>
					<ul class="list">
						<?php
						$levels = get_terms( 'level', array(
							'hide_empty' => false,
							'orderby' => 'id',
						) );

						foreach ($levels as $level) : ?>
						<li><a href="<?php echo get_post_type_archive_link( 'lesson-plan' ) . '?level[]=' . $level->term_id; ?>"><?php echo esc_html( $level->name ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
							<?php
						endforeach;
						?>
					</ul>
				</div>

				<div class="lesson-plan-duration">
					<h2 class="h4 lesson-plan-level-header"><?php echo esc_html__( 'Duration', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-level-description"><?php echo esc_html__( 'How long a lesson is estimated to take.', 'wporg-learn' ); ?></div>
					<ul class="list">
						<?php
						$durations = get_terms( 'duration', array(
							'hide_empty' => false,
							'orderby' => 'id',
						) );

						foreach ($durations as $duration) : ?>
						<li><a href="<?php echo get_post_type_archive_link( 'lesson-plan' ) . '?duration[]=' . $duration->term_id; ?>"><?php echo esc_html( $duration->name ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
							<?php
						endforeach;
						?>
					</ul>
				</div>
			</div>
		</section>

	</main><!-- #main -->

<?php
get_footer();
