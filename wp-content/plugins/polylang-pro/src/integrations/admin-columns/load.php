<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		if ( ! defined( 'AC_FILE' ) && ! defined( 'ACP_FILE' ) ) {
			// Run only if Admin Columns is enabled.
			return;
		}

		if ( ! did_action( 'pll_init' ) || ! PLL()->model->has_languages() ) {
			// Run only if Polylang (and its API) is loaded, and if there is at least one language.
			return;
		}

		PLL_Integrations::instance()->cpac = new PLL_CPAC();

		add_action( 'admin_init', array( PLL_Integrations::instance()->cpac, 'init' ) );
	}
);
