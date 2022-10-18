<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

get_header();
get_template_part( 'template-parts/component', 'breadcrumbs' );
?>

<main id="main" class="site-main">

<?php
if ( '' === get_query_var( 'search' ) && empty( $_GET ) && is_post_type_archive() ) :
	?>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<h1 class="section-heading_title h2"><?php esc_html_e( 'Lesson Plans', 'wporg-learn' ); ?></h1>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) . '?_view=all' ); ?>" class="button button-xlarge button-secondary">
				<?php esc_html_e( 'Browse all lesson plans', 'wporg-learn' ); ?>
			</a>
		</div>

		<hr>

		<div class="section-lp_description">
			<div class="row align-middle between gutters">
				<p class="section-lp_text col-8">
					<?php esc_html_e( 'Want to help others learn about WordPress? Read through, use, and remix these lesson plans.', 'wporg-learn' ); ?>
				</p>
				<?php
				set_query_var( 'post_type', 'lesson-plan' );
				get_template_part( 'template-parts/component', 'archive-search' );
				?>
			</div>
		</div>

		<hr>
		<?php
		$categories = get_terms( array(
			'taxonomy'   => 'wporg_lesson_category',
			'hide_empty' => false,
			'orderby'    => 'id',
			'order'      => 'DESC',
		) );
		?>
		<div class="lp-taxonomy">
			<h2 class="h4 lp-taxonomy-header"><?php echo esc_html__( 'Topic', 'wporg-learn' ); ?></h2>
			<div class="lp-taxonomy-description"><?php echo esc_html__( 'Browse lesson plans by their high-level topic.', 'wporg-learn' ); ?></div>
			<div class="card-grid card-grid_4">
				<?php foreach ( $categories as $category ) :
					$is_sticky = get_term_meta( $category->term_id, 'sticky', true );
					if ( $is_sticky ) :
						?>
					<a class="card button" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
						<?php $category_icon = get_term_meta( $category->term_id, 'dashicon-class', true ) ?? 'wordpress-alt'; ?>
						<div>
							<span aria-hidden="true" class="dashicons dashicons-<?php echo esc_attr( $category_icon ); ?>"></span>
						</div>
						<?php echo esc_html( $category->name ); ?>
					</a>
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
		<div class="lp-taxonomy">
			<h2 class="h4 lp-taxonomy-header"><?php echo esc_html__( 'Audience', 'wporg-learn' ); ?></h2>
			<div class="lp-taxonomy-description"><?php echo esc_html__( "Browse lesson plans by the audience they're intended for.", 'wporg-learn' ); ?></div>
			<div class="card-grid card-grid_4">
				<?php foreach ( $audiences as $audience ) :
					$is_sticky = get_term_meta( $audience->term_id, 'sticky', true );
					if ( $is_sticky ) :
						?>
					<a class="card button" href="<?php echo esc_url( get_term_link( $audience ) ); ?>">
						<div>
							<?php
							$audience_icon = get_term_meta( $audience->term_id, 'dashicon-class', true ) ?? 'wordpress-alt';
							?>
							<span aria-hidden="true" class="dashicons dashicons-<?php echo esc_attr( $audience_icon ); ?>"></span>
						</div>
						<?php echo esc_html( $audience->name ); ?>
					</a>
						<?php
				endif;
			endforeach; ?>
			</div>
		</div>

		<div class="row lp-two-col between">
			<div class="lp-level">
				<h2 class="h4 lp-taxonomy-header"><?php echo esc_html__( 'Level', 'wporg-learn' ); ?></h2>
				<div class="lp-taxonomy-description"><?php echo esc_html__( 'What experience participants need.', 'wporg-learn' ); ?></div>
				<ul class="lp-two-col-list">
					<?php
					$levels = get_terms( 'level', array(
						'hide_empty' => false,
						'orderby'    => 'id',
					) );

					foreach ( $levels as $level ) : ?>
					<li>
						<a class="button" href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?level[]=' . esc_html( $level->term_id ); ?>">
							<?php echo esc_html( $level->name ); ?><span aria-hidden="true" class="dashicons dashicons-arrow-right-alt2"></span>
						</a>
					</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>

			<div class="lp-duration">
				<h2 class="h4 lp-taxonomy-header"><?php echo esc_html__( 'Duration', 'wporg-learn' ); ?></h2>
				<div class="lp-taxonomy-description"><?php echo esc_html__( 'How long a lesson is estimated to take.', 'wporg-learn' ); ?></div>
				<ul class="lp-two-col-list">
					<?php
					$durations = get_terms( 'duration', array(
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					) );

					$duration_index   = 0;
					$any_duration_arr = array();
					$lp_archive_url   = get_post_type_archive_link( 'lesson-plan' );
					foreach ( $durations as $duration ) :
						if ( $duration_index < 3 ) :
							?>
					<li>
						<a class="button" href="<?php echo esc_url( add_query_arg( array( 'duration[]' => $duration->term_id ), $lp_archive_url ) ); ?>">
							<?php echo esc_html( $duration->name ); ?><span aria-hidden="true" class="dashicons dashicons-arrow-right-alt2"></span>
						</a>
					</li>
							<?php
						else :
							$any_duration_arr['duration'][] = $duration->term_id;
						endif;
						$duration_index++;
					endforeach;

					if ( $any_duration_arr['duration'] ) :
						?>
						<li>
							<a class="button" href="<?php echo esc_url( add_query_arg( $any_duration_arr, $lp_archive_url ) ); ?>">
								<?php echo esc_html__( '60 mins or longer', 'wporg-learn' ); ?><span aria-hidden="true" class="dashicons dashicons-arrow-right-alt2"></span>
							</a>
						</li>
						<?php
					endif;
					?>
				</ul>
			</div>
		</div>

		<?php
		$instruction_types = get_terms( 'instruction_type', array(
			'hide_empty' => false,
			'orderby'    => 'id',
		) );
		?>
		<div class="lp-taxonomy">
			<h2 class="h4 lp-taxonomy-header"><?php echo esc_html__( 'Format', 'wporg-learn' ); ?></h2>
			<div class="lp-taxonomy-description"><?php echo esc_html__( 'Browse lesson plans based on their format.', 'wporg-learn' ); ?></div>
			<div class="card-grid card-grid_4">
				<?php foreach ( $instruction_types as $instruction_type ) : ?>
				<a class="card button" href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) ) . '?type[]=' . esc_html( $instruction_type->term_id ); ?>">
					<?php echo esc_html( $instruction_type->name ); ?>
				</a>
				<?php endforeach; ?>
			</div>
		</div>

		<hr>

		<div class="row align-middle around lp-cta">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'lesson-plan' ) . '?_view=all' ); ?>" class="button button-xlarge button-secondary">
				<?php esc_html_e( 'Browse all lesson plans', 'wporg-learn' ); ?>
			</a>
		</div>
	</section>

<?php else : ?>
	<section>
		<div class="row align-middle between section-heading section-heading--with-space">
			<?php the_archive_title( '<h1 class="section-heading_title h2">', '</h1>' ); ?>
			<?php get_template_part( 'template-parts/component', 'archive-search' ); ?>
		</div>

		<hr>

		<div class="lp-archive-items row gutters between">
			<div class="card-grid col-9">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) :
						the_post();
						get_template_part(
							'template-parts/component',
							'card',
							wporg_learn_get_card_template_args( get_the_ID() )
						);
					endwhile; ?>
				<?php else : ?>
					<p class="not-found">
						<?php echo esc_html( get_post_type_object( 'lesson-plan' )->labels->not_found ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php get_template_part( 'template-parts/component', 'lesson-filters' ); ?>
		</div>

		<?php the_posts_pagination(); ?>
	</section>
	<hr>

	<?php get_template_part( 'template-parts/component', 'submit-idea-cta' ); ?>
<?php endif; ?>

</main>

<?php get_footer();
