<?php
/**
 * @package Polylang
 *
 * @param string   $ajax_action
 * @param string   $button_label
 * @param string   $id
 * @param string   $input_base_name
 * @param string   $message_default  Can contain `<a>`, and `<code>` tags.
 * @param string   $message_success  Can contain `<a>`, and `<code>` tags.
 * @param string[] $messages_warning Can contain `<a>`, and `<code>` tags.
 * @param string[] $messages_error   Can contain `<a>`, and `<code>` tags.
 * @param string   $option
 * @param string   $title
 * @param string   $value
 */

defined( 'ABSPATH' ) || exit;

$tags = array(
	'a'    => array(
		'href' => true,
	),
	'code' => true,
);
?>
<tr id="pll-<?php echo esc_attr( $atts['id'] ); ?>-label">
	<td>
		<label for="pll-<?php echo esc_attr( $atts['id'] ); ?>"><?php echo esc_html( $atts['title'] ); ?></label>
	</td>
	<td>
		<p>
			<?php
			printf(
				'<input id="pll-%s" name="%s[%s]" type="password" value="%s" class="regular-text code" data-name="%s"/>',
				esc_attr( $atts['id'] ),
				esc_attr( $atts['input_base_name'] ),
				esc_attr( $atts['option'] ),
				esc_attr( $atts['value'] ),
				esc_attr( $atts['option'] )
			);
			printf(
				'<button class="button button-secondary pll-ajax-button" type="button" data-action="%s" data-nonce="%s">%s</button>',
				esc_attr( $atts['ajax_action'] ),
				esc_attr( wp_create_nonce( $atts['ajax_action'] ) ),
				esc_html( $atts['button_label'] )
			);
			?>
			<span class="spinner"></span>
		</p>

		<?php
		/**
		 * The `message_default` is shown when no AJAX call has been made yet.
		 * The `message_success` is shown when the AJAX call is a success.
		 * A `messages_warning`/`messages_error` is shown if the class `pll-message-shown` is added to it.
		 */
		if ( ! empty( $atts['message_default'] ) ) {
			?>
			<p class="description pll-origin-message pll-message"><?php echo wp_kses( $atts['message_default'], $tags ); ?></p>
			<?php
		}
		if ( ! empty( $atts['message_success'] ) ) {
			?>
			<p class="description pll-success-message pll-message">
				<span class="pll-icon" aria-hidden="true">✓</span>
				<?php echo wp_kses( $atts['message_success'], $tags ); ?>
			</p>
			<?php
		}
		if ( ! empty( $atts['messages_warning'] ) ) {
			foreach ( $atts['messages_warning'] as $error_code => $error_message ) {
				?>
				<p class="description pll-warning-message pll-message <?php echo esc_attr( $error_code ); ?>">
				<span class="pll-icon" aria-hidden="true">✗</span>
				<span class="pll-error-message-text"></span>
				<?php echo wp_kses( $error_message, $tags ); ?>
			</p>
				<?php
			}
		}
		if ( ! empty( $atts['messages_error'] ) ) {
			foreach ( $atts['messages_error'] as $error_code => $error_message ) {
				?>
				<p class="description pll-error-message pll-message <?php echo esc_attr( $error_code ); ?>">
					<span class="pll-icon" aria-hidden="true">✗</span>
					<span class="pll-error-message-text"></span>
					<?php echo wp_kses( $error_message, $tags ); ?>
				</p>
				<?php
			}
		}
		?>
	</td>
</tr>
