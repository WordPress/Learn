<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Export_Post_Meta
 *
 * @since 3.3
 */
class PLL_Export_Post_Metas extends PLL_Export_Metas {
	/**
	 * Constructor.
	 *
	 * @since 3.3
	 */
	public function __construct() {
		$this->meta_type               = 'post';
		$this->import_export_meta_type = PLL_Import_Export::POST_META;
	}

	/**
	 * Returns the default post metas to export.
	 *
	 * @since 3.8
	 *
	 * @return array A multi-dimensional array containing nested meta sub keys to translate.
	 */
	protected function get_default_metas_to_export(): array {
		return array(
			'_wp_attachment_image_alt' => 1,
			'footnotes'                => array(
				'*' => array(
					'content' => 1,
				),
			),
		);
	}

	/**
	 * Returns the default encodings to use for post metas.
	 *
	 * @since 3.8
	 *
	 * @return array A multi-dimensional array containing nested meta sub keys to translate.
	 */
	protected function get_default_meta_encodings(): array {
		return array(
			'footnotes' => 'json',
		);
	}
}
