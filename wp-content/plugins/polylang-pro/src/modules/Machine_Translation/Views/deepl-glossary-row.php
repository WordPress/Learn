<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string $ajax_action     Name of the Ajax action to fetch the glossaries.
 *   @type string $option          Deepl `glossary` option name.
 *   @type string $description     Description displayed under the field. Can contain `<a>`, and `<code>` tags.
 *   @type string $input_base_name HTML `name` attribute of the input field.
 * }
 */

defined( 'ABSPATH' ) || exit;

$tags = array(
	'a'    => array( 'href' => true ),
	'code' => array(),
);

?>
<tr id="pll-deepl-glossary-label">
	<td><label for="pll-deepl-glossary"><?php esc_html_e( 'Glossary', 'polylang-pro' ); ?></label></td>
	<td>
		<?php
		printf(
			'<select id="pll-deepl-glossary" name="%s[%s]" data-action="%s" data-nonce="%s">',
			esc_attr( $atts['input_base_name'] ),
			esc_attr( $atts['option'] ),
			esc_attr( $atts['ajax_action'] ),
			esc_attr( wp_create_nonce( $atts['ajax_action'] ) )
		);
		?>
			<option value="">
				<?php
				/* translators: Empty choice for <select> box. */
				esc_html_e( '&mdash; Select &mdash;', 'polylang-pro' );
				?>
			</option>
		</select>
		<span class="spinner pll-spinner-inline"></span>

		<p class="description"><?php echo wp_kses( $atts['description'], $tags ); ?></p>
	</td>
</tr>
