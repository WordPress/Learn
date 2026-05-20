<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Widget_Blocks\Frontend_Filters;
use WP_Syntex\Polylang_Pro\Widget_Blocks\Language_Attribute;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

add_action(
	'pll_init',
	function ( $polylang ) {
		if ( $polylang->model->languages->has() && pll_use_block_editor_plugin() ) {
			if ( $polylang instanceof PLL_Frontend ) {
				$polylang->filters_widgets_blocks = new Frontend_Filters( $polylang );
			}

			$polylang->widget_editor = ( new Language_Attribute() )->init();
		}
	}
);
