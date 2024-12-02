<?php
/**
 * @package Polylang
 *
 * @param string $ajax_action
 */

defined( 'ABSPATH' ) || exit;

printf(
	'<div class="pll-progress-bar-wrapper" data-action="%s" data-nonce="%s"><span class="spinner"></span><div style="width: 0%%;"></div></div>',
	esc_attr( $atts['ajax_action'] ),
	esc_attr( wp_create_nonce( $atts['ajax_action'] ) )
);
