<?php
/**
 * @package Polylang-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

if ( ! $polylang->model->has_languages() ) {
	return;
}

add_action(
	'rest_api_init',
	static function () use ( $polylang ) {
		$polylang->rest_api = new PLL_REST_API( $polylang );
	}
);
