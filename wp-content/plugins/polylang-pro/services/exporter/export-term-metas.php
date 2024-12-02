<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Export_Term_Metas
 *
 * @since 3.3
 */
class PLL_Export_Term_Metas extends PLL_Export_Metas {

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 */
	public function __construct() {
		$this->meta_type               = 'term';
		$this->import_export_meta_type = PLL_Import_Export::TERM_META;
	}
}
