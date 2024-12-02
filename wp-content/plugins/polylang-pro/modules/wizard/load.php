<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( $polylang instanceof PLL_Admin_Base && Polylang::is_wizard() ) {
	add_action(
		'admin_init',
		function () use ( $polylang ) {
			$polylang->wizard_pro = new PLL_Wizard_Pro( $polylang );
		},
		30
	);
}
