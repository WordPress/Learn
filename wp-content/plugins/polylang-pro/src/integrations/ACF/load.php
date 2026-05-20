<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF;

use PLL_Integrations;

defined( 'ABSPATH' ) || exit;

Deactivate::add_hooks();

add_action(
	'after_setup_theme',
	function () {
		/*
		 * This must be checked only after the theme is loaded (not earlier than 'after_setup_theme') because some
		 * themes include ACF.
		 */
		if ( ! Main::can_use() ) {
			return;
		}

		if ( ! did_action( 'pll_init' ) || ! PLL()->model->has_languages() ) {
			// Run only if Polylang (and its API) is loaded, and if there is at least one language.
			return;
		}

		PLL_Integrations::instance()->acf = new Main();

		add_action( 'acf/init', array( PLL_Integrations::instance()->acf, 'on_acf_init' ) );
	}
);
