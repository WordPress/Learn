<?php
/**
 * Title: Sensei Lesson Actions
 * Slug: wporg-learn-2024/sensei-lesson-actions
 * Inserter: no
 *
 * Original source: https://github.com/Automattic/sensei/blob/af62fb1115daf2063bc56331a7d8b1b3ea805866/themes/sensei-course-theme/templates/default/lesson.php
 */

$prev_next_lessons = sensei_get_prev_next_lessons( get_the_ID() );
$prev_url = $prev_next_lessons['previous']['url'] ?? null;
$next_url = $prev_next_lessons['next']['url'] ?? null;
$is_completed = Sensei_Utils::user_completed_lesson( get_the_ID() );

?>

<!-- wp:sensei-lms/lesson-actions {"toggledBlocks":{"sensei-lms/button-reset-lesson":false}} -->
<div class="wp-block-sensei-lms-lesson-actions">
	<div class="sensei-buttons-container">

		<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper">
			<div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'Take quiz', 'sensei-lms' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-view-quiz -->

		<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true,"className":"is-style-outline"} -->
		<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-complete-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete lesson', 'sensei-lms' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-complete-lesson -->

		<?php if ( $is_completed && ( $prev_url || $next_url ) ) : ?>
			<!-- wp:buttons {"className":"sensei-lesson-actions-nav"} -->
			<div class="wp-block-buttons sensei-lesson-actions-nav">
				<?php if ( $prev_url ) : ?>
					<!-- wp:button {"className":"is-style-outline"} -->
					<div class="wp-block-button is-style-outline has-text-align-left">
						<a class="wp-block-button__link wp-element-button" href="<?php echo esc_attr( $prev_url ); ?>"><?php esc_html_e( 'Previous Lesson', 'wporg-learn' ); ?></a>
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
				<?php if ( $next_url ) : ?>
					<!-- wp:button {"className":"is-style-fill"} -->
					<div class="wp-block-button is-style-fill has-text-align-left">
						<a class="wp-block-button__link wp-element-button" href="<?php echo esc_attr( $next_url ); ?>"><?php esc_html_e( 'Next Lesson', 'wporg-learn' ); ?></a>
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
			</div>
			<!-- /wp:buttons -->
		<?php endif; ?>
	</div>
</div>
<!-- /wp:sensei-lms/lesson-actions -->
