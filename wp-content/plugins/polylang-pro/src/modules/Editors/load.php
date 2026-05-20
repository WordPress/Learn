<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Editors\Screens\Post;
use WP_Syntex\Polylang_Pro\Editors\Screens\Site;
use WP_Syntex\Polylang_Pro\Editors\Screens\Widget;
use WP_Syntex\Polylang_Pro\Editors\Filter_Preload_Paths;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

add_action(
	'pll_init',
	function ( $polylang ) {
		if (
			$polylang->model->languages->has()
			&& $polylang instanceof PLL_Admin
			&& pll_use_block_editor_plugin()
		) {
			$polylang->site_editor   = ( new Site( $polylang ) )->init();
			$polylang->post_editor   = ( new Post( $polylang ) )->init();
			$polylang->widget_editor = ( new Widget( $polylang ) )->init();
			$polylang->filter_path   = ( new Filter_Preload_Paths( $polylang ) )->init();
		}
	}
);
