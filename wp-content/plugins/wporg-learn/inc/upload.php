<?php
/**
 * Upload settings for Learn.WordPress.org.
 *
 * @package WPOrg_Learn
 */

namespace WPOrg_Learn\Upload;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'upload_mimes', __NAMESPACE__ . '\allow_zip_uploads' );

/**
 * Allow ZIP file uploads on Learn.WordPress.org only.
 *
 * Activity Kit editors need to upload ZIP archives containing kit materials.
 * The filter is gated on blog ID 7 (learn.wordpress.org on the WordPress.org
 * network) so it does not enable ZIP uploads across the entire network.
 *
 * @param string[] $mimes Allowed MIME types keyed by file extension.
 * @return string[]
 */
function allow_zip_uploads( $mimes ) {
	if ( 7 !== get_current_blog_id() ) {
		return $mimes;
	}

	$mimes['zip'] = 'application/zip';

	return $mimes;
}
