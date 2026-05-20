<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string   $option          Deepl `formality` option name.
 *   @type string[] $formal          List of language names with their locale. Can contain `<code>` tags.
 *                                   Example array( 'Deutsch (<code>de_DE_formal</code>)', 'Nederlands (<code>nl_NL_formal</code>)').
 *   @type string[] $informal        List of language names with their locale. Can contain `<code>` tags.
 *                                   Example array( 'Deutsch (<code>de_CH_informal</code>)').
 *   @type string   $input_base_name HTML `name` attribute of the input field.
 *   @type string   $value           Current Deepl `formality` option value.
 * }
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
				if ( 1 === count( $atts['formal'] ) ) {
					/* translators: %s is a language name. */
					$message = __( 'Note: formal tone will always be used for %s language.', 'polylang-pro' );
				} else {
					/* translators: %s is a list of language names. */
					$message = __( 'Note: formal tone will always be used for %s languages.', 'polylang-pro' );
				}
				printf( esc_html( $message ), wp_kses( wp_sprintf_l( '%l', $atts['formal'] ), array( 'code' => array() ) ) );
				?>
			</p>
			<?php
		}

		if ( ! empty( $atts['informal'] ) ) {
			?>
			<p class="description">
				<?php
				if ( 1 === count( $atts['informal'] ) ) {
					/* translators: %s is a language name. */
					$message = __( 'Note: informal tone will always be used for %s language.', 'polylang-pro' );
				} else {
					/* translators: %s is a list of language names. */
					$message = __( 'Note: informal tone will always be used for %s languages.', 'polylang-pro' );
				}
				printf( esc_html( $message ), wp_kses( wp_sprintf_l( '%l', $atts['informal'] ), array( 'code' => array() ) ) );
				?>
			</p>
			<?php
		}
		?>
	</td>
</tr>
