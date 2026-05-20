<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string     $message   Notice to display. Can contain `<br>` and `<code>` tags.
 *   @type string     $type      Optional type of notice. Possible values are `success`, `warning`, `error`, and `info`. Default is `error`.
 *   @type string     $name      Name of the machine translation service.
 *   @type string[]   $languages List of language names with their locale. Can contain `<code>` tags.
 *                               Example array( 'Afrikaans (<code>af</code>)' ).
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $atts['languages'] ) ) {
	$atts['type'] = 'warning';
	if ( 1 === count( $atts['languages'] ) ) {
		/* translators: %1$s is a machine translation service's name, %2$s is a language name (and its locale). */
		$atts['message'] = __( 'The following language is not available in %1$s: %2$s.', 'polylang-pro' );
	} else {
		/* translators: %1$s is a machine translation service's name, %2$s is a list of language names (and their locale). */
		$atts['message'] = __( 'The following languages are not available in %1$s: %2$s.', 'polylang-pro' );
	}
	$atts['message'] = sprintf( $atts['message'], $atts['name'], wp_sprintf_l( '%l', $atts['languages'] ) );

	include __DIR__ . '/inner-notice.php';
}
