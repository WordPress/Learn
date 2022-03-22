<?php
/**
 * The Template for rendering the Lesson Drip email.
 *
 * Override this template by copying it to yourtheme/sensei-content-drip/single-email-drip-notification.php
 *
 * @author   Automattic
 * @package  sensei-content-drip
 * @category Templates
 * @version  2.0.0
 */
?>

<p><?php echo esc_html( $email_greeting ); ?></p>

<p><?php echo esc_html( $email_body ); ?></p>

<p>
	<ul>
		<?php foreach ( $courses_and_lessons as $course_id => $lesson_data_items ) { ?>
			<?php foreach ( $lesson_data_items as $lesson_id => $lesson_data ) { ?>

			<li>
				<a href="<?php echo esc_url( $lesson_data['url'] ); ?>">
					<?php echo esc_html( $lesson_data['title'] ); ?>
				</a>
			</li>

			<?php } ?>
		<?php } ?>
	</ul>
</p>

<p><?php echo wp_kses_post( $email_footer ); ?></p>
