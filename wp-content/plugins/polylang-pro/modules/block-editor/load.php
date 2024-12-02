<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( $polylang->model->has_languages() && pll_use_block_editor_plugin() ) {
			if ( $polylang instanceof PLL_Admin ) {
				$polylang->block_editor_plugin = new PLL_Block_Editor_Plugin( $polylang );
			}

			if ( $polylang instanceof PLL_Frontend ) {
				$polylang->filters_widgets_blocks = new PLL_Frontend_Filters_Widgets_Blocks( $polylang );
			}

			$polylang->widget_editor = new PLL_Widget_Editor_Language_Attribute();
			$polylang->switcher_block = ( new PLL_Language_Switcher_Block( $polylang ) )->init();
			$polylang->navigation_block = ( new PLL_Navigation_Language_Switcher_Block( $polylang ) )->init();
		}
	}
);
