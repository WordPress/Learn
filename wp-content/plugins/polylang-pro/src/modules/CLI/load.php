<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI;

defined( 'ABSPATH' ) || exit;

use PLL_Admin;
use WP_Syntex\Polylang_Pro\Modules\CLI\Commands;

if ( defined( 'WP_CLI' ) && \WP_CLI && $polylang instanceof PLL_Admin ) {
	( new Commands( $polylang->model ) )->register();
}
