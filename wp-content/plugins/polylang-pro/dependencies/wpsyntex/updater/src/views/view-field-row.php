<?php
/**
 * Displays a license field.
 *
 * Note: the name of the input is intentionally not prefixed to easily manage
 * the Polylang legacy licenses system at the same time.
 *
 * @package Polylang Updater
 *
 * @since 1.0
 *
 * @var array $atts {
 *     @type string $id          ID of the plugin.
 *     @type string $name        Name of the plugin.
 *     @type string $license_key License key.
 *     @type string $row_class   HTML class for the row.
 *     @type string $button_text Optional. Text for the deactivation button.
 *     @type string $message     Optional. Message displayed under the field.
 * }
 */

use WP_Syntex\Polylang_Pro\Updater\License;

defined( 'ABSPATH' ) || exit;

$tags = array(
	'a' => array(
		'href'   => array(
			'values' => array( License::ACCOUNT_URL ),
		),
		'target' => true,
	),
);

?>
<tr id="pllu-license-<?php echo esc_attr( $atts['id'] ); ?>" class="<?php echo esc_attr( $atts['row_class'] ); ?>">
	<td><label for="pllu-licenses[<?php echo esc_attr( $atts['id'] ); ?>]"><?php echo esc_html( $atts['name'] ); ?></label></td>
	<td><input name="licenses[<?php echo esc_attr( $atts['id'] ); ?>]" id="pllu-licenses[<?php echo esc_attr( $atts['id'] ); ?>]" type="password" value="<?php echo esc_attr( $atts['license_key'] ); ?>" class="regular-text code" />
	<?php
	if ( ! empty( $atts['button_text'] ) ) {
		?>
		<button id="deactivate_<?php echo esc_attr( $atts['id'] ); ?>" type="button" class="button button-secondary pllu-deactivate-license"><?php echo esc_html( $atts['button_text'] ); ?></button>
		<?php
	}
	if ( ! empty( $atts['message'] ) ) {
		?>
		<p><?php echo wp_kses( $atts['message'], $tags ); ?></p>
		<?php
	}
	?>
</tr>
