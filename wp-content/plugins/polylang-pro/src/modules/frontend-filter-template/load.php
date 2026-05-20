<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit; // @phpstan-ignore-line

if ( ! $polylang instanceof PLL_Frontend ) {
	return;
}

/**
 * Whether or not Polylang should filter templates.
 *
 * @since 3.7
 *
 * @param bool $filter_templates Whether or not Polylang should filter templates.
 *                               Defaults to true when the current theme is a block theme, false otherwise.
 */
$filter_templates = apply_filters( 'pll_filtered_templates', wp_is_block_theme() );
if ( $filter_templates ) {
	$polylang->filtered_templates = ( new PLL_Filter_Templates( $polylang ) )->init();
}
