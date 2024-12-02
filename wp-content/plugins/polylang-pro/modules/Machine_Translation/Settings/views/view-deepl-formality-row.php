<?php
/**
 * @package Polylang-Pro
 *
 * @param string[] $formal   Messages can contain `<code>` tags.
 * @param string[] $informal Messages can contain `<code>` tags.
 * @param string   $input_base_name
 * @param string   $value
 */

defined( 'ABSPATH' ) || exit;

?>
<tr id="pll-deepl-formality-label">
	<td><label for="pll-deepl-formality"><?php esc_html_e( 'Formality', 'polylang-pro' ); ?></label></td>
	<td>
		<select id="pll-deepl-formality" name="<?php echo esc_attr( $atts['input_base_name'] ); ?>[<?php echo esc_attr( $atts['option'] ); ?>]">
			<?php
			$values = array(
				'default'     => _x( 'Default', 'Language formality', 'polylang-pro' ),
				'prefer_more' => __( 'Prefer formal', 'polylang-pro' ),
				'prefer_less' => __( 'Prefer informal', 'polylang-pro' ),
			);
			foreach ( $values as $value => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( (string) $value ),
					selected( $atts['value'], $value, false ),
					esc_html( $label )
				);
			}
			?>
		</select>

		<p class="description">
			<?php esc_html_e( 'Sets whether the translated text should lean on formal or informal language. Automatic fallback on the default formality if not available for the targeted language.', 'polylang-pro' ); ?>
		</p>

		<?php
		if ( ! empty( $atts['formal'] ) ) {
			?>
			<p class="description">
				<?php
				printf(
					/* translators: %s is a list of language names. */
					esc_html( _n( 'Note: formal tone will always be used for %s language.', 'Note: formal tone will always be used for %s languages.', count( $atts['formal'] ), 'polylang-pro' ) ),
					wp_kses( wp_sprintf_l( '%l', $atts['formal'] ), array( 'code' => true ) )
				);
				?>
			</p>
			<?php
		}

		if ( ! empty( $atts['informal'] ) ) {
			?>
			<p class="description">
				<?php
				printf(
					/* translators: %s is a list of language names. */
					esc_html( _n( 'Note: informal tone will always be used for %s language.', 'Note: informal tone will always be used for %s languages.', count( $atts['informal'] ), 'polylang-pro' ) ),
					wp_kses( wp_sprintf_l( '%l', $atts['informal'] ), array( 'code' => true ) )
				);
				?>
			</p>
			<?php
		}
		?>
	</td>
</tr>
