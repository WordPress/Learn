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
		<?php if ( $prev_url || $next_url ) : ?>
			<!-- wp:buttons {"className":"sensei-lesson-actions-nav"} -->
			<div class="wp-block-buttons sensei-lesson-actions-nav">
				<?php if ( $prev_url ) : ?>
					<!-- wp:button {"className":"has-text-align-center is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-font-size has-text-align-center is-style-outline has-inter-font-family has-normal-font-size">
						<a class="wp-block-button__link wp-element-button" style="font-weight:600;line-height:1" href="<?php echo esc_attr( $prev_url ); ?>"><?php esc_html_e( 'Previous Lesson', 'wporg-learn' ); ?></a>
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
				<?php if ( $next_url ) : ?>
					<!-- wp:button {"className":"has-text-align-center is-style-outline","fontSize":"normal","fontFamily":"inter"} -->
					<div class="wp-block-button has-custom-font-size has-text-align-center is-style-outline has-inter-font-family has-normal-font-size">
						<a class="wp-block-button__link wp-element-button" style="font-weight:600;line-height:1" href="<?php echo esc_attr( $next_url ); ?>"><?php esc_html_e( 'Next Lesson', 'wporg-learn' ); ?></a>
					</div>
					<!-- /wp:button -->
				<?php endif; ?>
			</div>
			<!-- /wp:buttons -->
		<?php endif; ?>

		<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-view-quiz sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper">
			<div class="wp-block-sensei-lms-button-view-quiz wp-block-sensei-button wp-block-button has-text-align-center has-inter-font-family has-normal-font-size">
				<button class="wp-block-button__link"><?php esc_html_e( 'Take quiz to complete lesson', 'wporg-learn' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-view-quiz -->

		<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-complete-lesson sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-complete-lesson wp-block-sensei-button wp-block-button has-text-align-center has-inter-font-family has-normal-font-size">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete lesson', 'sensei-lms' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-complete-lesson -->
	</div>
</div>
<!-- /wp:sensei-lms/lesson-actions -->
