<?php
/**
 * @package Polylang-Pro
 *
 * @param string     $name
 * @param string[][] $languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $atts['languages'] ) ) {
	$atts['type']    = 'warning';
	$atts['message'] = sprintf(
		/* translators: %1$s is the service's name, %2$s is a list of language names (and their locale). */
		_n( 'The following language is not available in %1$s: %2$s.', 'The following languages are not available in %1$s: %2$s.', count( $atts['languages'] ), 'polylang-pro' ),
		$atts['name'],
		wp_sprintf_l( '%l', $atts['languages'] )
	);
	include __DIR__ . '/view-inner-notice.php';
}
