<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit; // @phpstan-ignore-line

if ( ! $polylang->model->has_languages() ) {
	return;
}

add_action(
	'pll_init',
	function ( $polylang ) {
		$pll_fse_sub_modules = array(
			PLL_FSE_Default_Language_Change::class,
			PLL_FSE_Language::class,
			PLL_FSE_Language_Slug_Change::class,
			PLL_FSE_Filter_Block_Types::class,
			PLL_FSE_Post_Types::class,
			PLL_FSE_Query_Filters::class,
			PLL_FSE_REST_Duplicate_Template::class,
			PLL_FSE_REST_Enforce_Default_Template::class,
			PLL_FSE_Post_Deletion::class,
			PLL_FSE_Template_Model::class,
			PLL_FSE_Template_Slug_Sync::class,
		);

		foreach ( $pll_fse_sub_modules as $pll_fse_class ) {
			$polylang->{$pll_fse_class::get_name()} = ( new $pll_fse_class( $polylang ) )->init();
		}

		if ( $polylang->model instanceof PLL_Admin_Model ) {
			$polylang->{PLL_FSE_Recreate_Language::get_name()} = ( new PLL_FSE_Recreate_Language( $polylang ) )->init();
		}

		// PLL_FSE_REST_Template is required only in a REST context.
		add_action(
			'rest_api_init',
			function () use ( $polylang ) {
				$polylang->rest_api->template = ( new PLL_FSE_REST_Template( $polylang->rest_api, PLL_FSE_Tools::get_template_post_types() ) )->init();
			},
			20 // Load the FSE modules after the PLL_REST_API.
		);

		unset( $pll_fse_sub_modules, $pll_fse_class );
	},
	20 // Load the FSE modules after the PLL_REST_API.
);
