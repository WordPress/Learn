<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Site_Health;

/**
 * Add debug info in WP Site Health.
 *
 * @since 3.7
 */
class Info {
	/**
	 * Constructor.
	 *
	 * @since 3.7
	 */
	public function __construct() {
		add_filter( 'site_status_test_php_modules', array( $this, 'site_status_test_php_modules' ) );
	}

	/**
	 * Requires libxml and ZipArchive in Site Health.
	 *
	 * @since 3.7
	 *
	 * @param array $modules An associative array of modules to test for.
	 * @return array
	 */
	public function site_status_test_php_modules( $modules ) {
		$modules['mod_xml'] = array(
			'extension' => 'libxml',
			'required'  => true,
		);
		$modules['zip']     = array(
			'class'    => 'ZipArchive',
			'required' => true,
		);
		return $modules;
	}
}
