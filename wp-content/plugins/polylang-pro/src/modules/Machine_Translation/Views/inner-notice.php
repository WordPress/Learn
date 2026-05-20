<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string $message Notice to display. Can contain `<br>` and `<code>` tags.
 *   @type string $slug    Slug of the machine translation service.
 *   @type string $type    Optional. Possible values are `success`, `warning`, `error`, and `info`. Default is `error`.
 * }
 */

defined( 'ABSPATH' ) || exit;

$tags = array(
	'br'   => array(),
	'code' => array(),
);

$atts['type'] = ! empty( $atts['type'] ) && in_array( $atts['type'], array( 'success', 'warning', 'info' ), true ) ? $atts['type'] : 'error';
?>
<div class="pll-<?php echo esc_attr( $atts['slug'] ); ?>-notice pll-inner-notice notice-<?php echo esc_attr( $atts['type'] ); ?>">
	<p><strong><?php echo wp_kses( $atts['message'], $tags ); ?></strong></p>
</div>
