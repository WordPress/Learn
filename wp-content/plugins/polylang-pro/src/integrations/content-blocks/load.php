<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'pll_init',
	function () {
		if ( ! function_exists( 'custom_post_widget_plugin_init' ) ) {
			// Run only if Content Blocks is enabled.
			return;
		}

		PLL_Integrations::instance()->content_blocks = new PLL_Content_Blocks();
		PLL_Integrations::instance()->content_blocks->init();
	}
);
