<?php
/**
 * Loads the site health.
 *
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Site_Health\Info;

defined( 'ABSPATH' ) || exit;

if ( $polylang instanceof PLL_Admin && $polylang->model->has_languages() ) {
	$polylang->site_health_pro = new Info();
}
