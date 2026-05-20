<?php
/**
 * @package Polylang-Pro
 */

/**
 * Handles the admin action of exporting strings translations.
 *
 * @since 3.6
 */
class PLL_Export_Data_From_Strings {
	/**
	 * Used to query languages and translations.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Model $model  Polylang model.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;
	}

	/**
	 * Prepares and exports the selected strings translations.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $container       Export container.
	 * @param array                $sources         Curated list of strings to export.
	 * @param PLL_Language         $target_language The target language.
	 * @param bool                 $no_update       Whether to remove already translated strings. Default to false.
	 * @return WP_Error                             A `WP_Error` object. Note: an "empty" `WP_Error` object is returned on success.
	 */
	public function send_to_export( PLL_Export_Container $container, array $sources, PLL_Language $target_language, bool $no_update = false ): WP_Error {
		$source_language = $this->model->get_default_language();

		if ( empty( $source_language ) ) {
			return new WP_Error( 'pll_export_no_source_language', __( 'Error: Default language not defined.', 'polylang-pro' ) );
		}

		if ( $no_update ) {
			$mo = new PLL_MO();
			$mo->import_from_db( $target_language );
			$sources = array_filter(
				$sources,
				function ( $source ) use ( $mo ) {
					return empty( $mo->translate_if_any( $source['string'] ) );
				}
			);
		}

		if ( empty( $sources ) ) {
			return new WP_Error( 'pll_export_no_strings', __( 'Error: No strings found.', 'polylang-pro' ) );
		}

		( new PLL_Export_Strings( $this->model ) )->add_items( $container, $sources, $target_language );

		return new WP_Error();
	}
}
