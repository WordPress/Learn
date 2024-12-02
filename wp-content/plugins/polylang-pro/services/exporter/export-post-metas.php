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
	 * Get the meta names to export.
	 *
	 * @since 3.3
	 *
	 * @param int $from ID of the source object.
	 * @param int $to   ID of the target object.
	 * @return string[] List of custom fields names.
	 */
	protected function get_meta_names_to_export( int $from, int $to ): array {
		$default_metas_to_export = array(
			'_wp_attachment_image_alt' => 1,
			'footnotes'                => array(
				'*' => array(
					'content' => 1,
				),
			),
		);

		/** This filter is documented in modules/import-export/export/export-metas.php */
		return (array) apply_filters( "pll_{$this->meta_type}_metas_to_export", $default_metas_to_export, $from, $to );
	}

	/**
	 * Returns the meta formats.
	 *
	 * @since 3.6
	 *
	 * @param int $from ID of the source object.
	 * @param int $to   ID of the target object.
	 * @return array List of custom fields formats.
	 */
	protected function get_meta_encodings( int $from, int $to ): array {
		$formats = array(
			'footnotes' => 'json',
		);

		/** This filter is documented in modules/import-export/export/export-metas.php */
		return (array) apply_filters( "pll_{$this->meta_type}_meta_encodings", $formats, $from, $to );
	}
}
