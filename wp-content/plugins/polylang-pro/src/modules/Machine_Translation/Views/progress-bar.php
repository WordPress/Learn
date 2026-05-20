<?php
/**
 * @package Polylang-Pro
 *
 * @var array $atts {
 *   @type string $ajax_action Name of the Ajax action to update the progress bar.
 * }
 */

defined( 'ABSPATH' ) || exit;

printf(
	'<div class="pll-progress-bar-wrapper" data-action="%s" data-nonce="%s"><span class="spinner pll-spinner-inline"></span><div style="width: 0%%;"></div></div>',
	esc_attr( $atts['ajax_action'] ),
	esc_attr( wp_create_nonce( $atts['ajax_action'] ) )
);
