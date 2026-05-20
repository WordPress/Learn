<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities;

defined( 'ABSPATH' ) || exit;

if ( $polylang->model->has_languages() ) {
	require_once POLYLANG_PRO_DIR . '/src/modules/sync-post/load.php'; // Make sure `$polylang->sync_post_model` is set.

	$polylang->model->languages->register_proxy( new Languages_Proxy() );
	$polylang->advanced_capabilities = new Advanced_Capabilities( $polylang );
}
