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
			<div class="row align-middle between section-heading section-heading--with-space large-space">
				<?php the_title( '<h1 class="section-heading_title bigger">', '</h1>' ); ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>" class="button button-large button-secondary">
					<?php esc_html_e( 'Browse all lesson plans', 'wporg-learn' ); ?>
				</a>
			</div>

			<hr>

			<div class="row align-middle between section-plan_description">
				<div class="section-plan_text">
					<?php the_content(); ?>
				</div>
				<?php
				set_query_var( 'post_type', 'lesson-plan' );
				get_template_part( 'template-parts/component', 'archive-search' );
				?>
			</div>

			<hr>
			<?php
			$categories = get_terms( 'wporg_lesson_category', array(
				'hide_empty' => false,
				'orderby'    => 'id',
			) );
			?>
			<div class="row lesson-plan-taxonomy categories">
				<div class="card-grid card-grid_4">
					<h2 class="h4 lesson-plan-taxonomy-header"><?php echo esc_html__( 'Topic', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-taxonomy-description"><?php echo esc_html__( 'Browse lesson plans by their high-level topic.', 'wporg-learn' ); ?></div>
					<?php foreach ( $categories as $category ) :
						$is_sticky = get_term_meta( $category->term_id, 'sticky', true );
						if ( $is_sticky ) :
							?>
					<div class="card">
						<div class="icon">
							<?php
							$category_icon = get_term_meta( $category->term_id, 'dashicon-class', true );
							?>
							<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $category_icon ); ?>"></span></a>
						</div>
						<p class="taxonomy-title"><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></p>
					</div>
							<?php
						endif;
					endforeach; ?>
				</div>
			</div>

			<?php
			$audiences = get_terms( 'audience', array(
				'hide_empty' => false,
				'orderby'    => 'id',
			) );
			?>
			<div class="row lesson-plan-taxonomy">
				<div class="card-grid card-grid_4">
					<h2 class="h4 lesson-plan-taxonomy-header"><?php echo esc_html__( 'Audience', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-taxonomy-description"><?php echo esc_html__( 'Browse lesson plans by the audience they\'re intended for.', 'wporg-learn' ); ?></div>
					<?php foreach ( $audiences as $audience ) :
						$is_sticky = get_term_meta( $audience->term_id, 'sticky', true );
						if ( $is_sticky ) :
							?>
					<div class="card">
						<div class="icon">
							<?php
							$audience_icon = get_term_meta( $audience->term_id, 'dashicon-class', true );
							?>
							<a href="<?php echo esc_url( get_term_link( $audience ) ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( $audience_icon ); ?>"></span></a>
						</div>
						<p class="taxonomy-title"><a href="<?php echo esc_url( get_term_link( $audience ) ); ?>"><?php echo esc_html( $audience->name ); ?></a></p>
					</div>
							<?php
					endif;
				endforeach; ?>
				</div>
			</div>

			<div class="row lesson-plan-two-col between">
				<div class="lesson-plan-taxonomy">
					<h2 class="h4 lesson-plan-taxonomy-header"><?php echo esc_html__( 'Level', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-taxonomy-description"><?php echo esc_html__( 'What experience partipants need.', 'wporg-learn' ); ?></div>
					<ul class="list">
						<?php
						$levels = get_terms( 'level', array(
							'hide_empty' => false,
							'orderby'    => 'id',
						) );

						foreach ( $levels as $level ) : ?>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?level[]=' . esc_html( $level->term_id ); ?>"><?php echo esc_html( $level->name ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
							<?php
						endforeach;
						?>
					</ul>
				</div>

				<div class="lesson-plan-duration">
					<h2 class="h4 lesson-plan-taxonomy-header"><?php echo esc_html__( 'Duration', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-taxonomy-description"><?php echo esc_html__( 'How long a lesson is estimated to take.', 'wporg-learn' ); ?></div>
					<ul class="list">
						<?php
						$durations = get_terms( 'duration', array(
							'hide_empty' => false,
							'orderby'    => 'name',
							'order'      => 'ASC',
						) );

						$duration_index = 0;
						$any_duration_arr = array();
						foreach ( $durations as $duration ) :
							if ( $duration_index < 3 ) :
								?>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?duration[]=' . esc_html( $duration->term_id ); ?>"><?php echo esc_html( $duration->name ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
								<?php
							else :
								$any_duration_arr[] = 'duration[]=' . $duration->term_id;
							endif;
							$duration_index++;
						endforeach;

						if ( $any_duration_arr ) :
							$any_duration_html = implode( '&', $any_duration_arr );
							?>
							<li><a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?' . esc_html( $any_duration_html ); ?>"><?php echo esc_html__( 'Any(over 60m)', 'wporg-learn' ); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
							<?php
						endif;
						?>
					</ul>
				</div>
			</div>

			<?php
			$instruction_types = get_terms( 'instruction_type', array(
				'hide_empty' => false,
				'orderby' => 'id',
			) );
			?>
			<div class="row lesson-plan-taxonomy last-grid">
				<div class="card-grid card-grid_4">
					<h2 class="h4 lesson-plan-taxonomy-header"><?php echo esc_html__( 'Format', 'wporg-learn' ); ?></h2>
					<div class="lesson-plan-taxonomy-description"><?php echo esc_html__( 'Browse lesson plans based on their format.', 'wporg-learn' ); ?></div>
					<?php foreach ( $instruction_types as $instruction_type ) : ?>
					<div class="card">
						<p class="taxonomy-title"><a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?type[]=' . esc_html( $instruction_type->term_id ); ?>"><?php echo esc_html( $instruction_type->name ); ?></a></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<hr>

			<div class="row align-middle around lesson-plan-cta">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ); ?>" class="button button-large button-secondary">
					<?php esc_html_e( 'Browse all lesson plans', 'wporg-learn' ); ?>
				</a>
			</div>
		</section>

	</main><!-- #main -->

<?php
get_footer();
