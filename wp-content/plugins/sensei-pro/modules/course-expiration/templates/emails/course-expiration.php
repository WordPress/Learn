<?php
/**
 * The template for course expiration email.
 *
 * Override this template by copying it to your theme/sensei-wc-paid-courses/emails/course-expiration.php.
 *
 * @author  Automattic
 * @package Sensei Pro\Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before email content.
 *
 * @since 1.0.0
 * @hook sensei_wc_paid_courses_expiration_before_email_content
 */
do_action( 'sensei_wc_paid_courses_expiration_before_email_content' );
?>

<div>
	<p><?php echo esc_html( $body ); ?></p>
</div>

<?php
if ( ! empty( $actions ) ) {
	?>
	<ul>
		<?php
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		foreach ( $actions as $act ) {
			?>
			<li><a href="<?php echo esc_attr( $act['href'] ); ?>" target="_blank"><?php echo esc_attr( $act['label'] ); ?></a></li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * After email content.
 *
 * @since 1.0.0
 * @hook sensei_wc_paid_courses_expiration_after_email_content
 */
do_action( 'sensei_wc_paid_courses_expiration_after_email_content' );
?>
