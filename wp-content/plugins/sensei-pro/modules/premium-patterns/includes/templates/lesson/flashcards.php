<?php
/**
 * Flashcards lesson pattern template file.
 *
 * @package  sensei-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- wp:sensei-pro/flashcard -->
<div class="sensei-lms-flashcards__card-wrapper wp-block-sensei-pro-flashcard" data-sensei-wp-block="{}">
	<div class="sensei-lms-flip sensei-lms-flip--flipped-front">
		<!--sensei:inner-blocks-->
		<!-- wp:cover {"customOverlayColor":"#ffffff"} -->
		<div class="wp-block-cover has-background-dim" style="background-color:#ffffff">
			<div class="wp-block-cover__inner-container">
				<!-- wp:paragraph {"align":"center","placeholder":"<?php esc_html_e( 'Add flash card question', 'sensei-pro' ); ?>","style":{"color":{"text":"#000000"}},"fontSize":"large"} -->
				<p class="has-text-align-center has-text-color has-large-font-size" style="color:#000000"></p>
				<!-- /wp:paragraph -->
			</div>
		</div>
		<!-- /wp:cover -->

		<!-- wp:cover {"customOverlayColor":"#ffffff"} -->
		<div class="wp-block-cover has-background-dim" style="background-color:#ffffff">
			<div class="wp-block-cover__inner-container">
				<!-- wp:paragraph {"align":"center","placeholder":"<?php esc_html_e( 'Add flash card answer', 'sensei-pro' ); ?>","style":{"color":{"text":"#000000"}},"fontSize":"large"} -->
				<p class="has-text-align-center has-text-color has-large-font-size" style="color:#000000"></p>
				<!-- /wp:paragraph -->
			</div>
		</div>
		<!-- /wp:cover -->
		<!--/sensei:inner-blocks-->
		<button class="sensei-lms-flip__button" tabindex="0">
			<a tabindex="-1">
				<?php
					// translators: verb + noun, refers to an action of flipping a card.
					esc_html_e( 'Flip Card', 'sensei-pro' );
				?>
			</a>
		</button>
	</div>
</div>
<!-- /wp:sensei-pro/flashcard -->

<!-- wp:sensei-lms/lesson-actions -->
<div class="wp-block-sensei-lms-lesson-actions">
	<div class="sensei-buttons-container">
		<!-- wp:sensei-lms/button-view-quiz {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-view-quiz is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-view-quiz__wrapper">
			<div class="wp-block-sensei-lms-button-view-quiz is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'View Quiz', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-view-quiz -->

		<!-- wp:sensei-lms/button-complete-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-complete-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-complete-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-complete-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Complete Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-complete-lesson -->

		<!-- wp:sensei-lms/button-next-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-next-lesson is-style-default sensei-buttons-container__button-block wp-block-sensei-lms-button-next-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-next-lesson is-style-default wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link"><?php esc_html_e( 'Next Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-next-lesson -->

		<!-- wp:sensei-lms/button-reset-lesson {"inContainer":true} -->
		<div class="wp-block-sensei-lms-button-reset-lesson is-style-outline sensei-buttons-container__button-block wp-block-sensei-lms-button-reset-lesson__wrapper">
			<div class="wp-block-sensei-lms-button-reset-lesson is-style-outline wp-block-sensei-button wp-block-button has-text-align-left">
				<button class="wp-block-button__link sensei-stop-double-submission"><?php esc_html_e( 'Reset Lesson', 'sensei-pro' ); ?></button>
			</div>
		</div>
		<!-- /wp:sensei-lms/button-reset-lesson -->
	</div>
</div>
<!-- /wp:sensei-lms/lesson-actions -->
