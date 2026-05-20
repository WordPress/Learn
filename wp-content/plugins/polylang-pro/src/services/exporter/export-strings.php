<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class allowing to add string translations to an export.
 *
 * @since 3.6
 *
 * @phpstan-type exportSource array{
 *     string: non-falsy-string,
 *     name: non-falsy-string,
 *     context: string
 * }
 */
class PLL_Export_Strings {
	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Used to query translations.
	 * The array keys are target language slugs (the source language is always the default one, so there is no need to
	 * differentiate translations by the source language).
	 *
	 * @var PLL_MO[]
	 *
	 * @phpstan-var array<non-empty-string, PLL_MO>
	 */
	private $mo = array();

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Model $model Polylang model.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;
	}

	/**
	 * Adds multiple string translations to an export.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $export_container Export container.
	 * @param string[][]           $items            Items to export.
	 * @param PLL_Language         $target_language  Language to translate into.
	 * @return void
	 *
	 * @phpstan-param non-empty-array<exportSource> $items
	 */
	public function add_items( PLL_Export_Container $export_container, array $items, PLL_Language $target_language ) {
		$source_language = $this->model->get_default_language();

		if ( empty( $source_language ) ) {
			return;
		}

		$export = $export_container->get( $source_language, $target_language );

		// Caching is done in `add_item()`.
		foreach ( $items as $item ) {
			$this->add_item( $export, $item );
		}
	}

	/**
	 * Adds one string translation to an export.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Data $export Export object.
	 * @param string[]        $item   Item to export.
	 * @return void
	 *
	 * @phpstan-param exportSource $item
	 */
	public function add_item( PLL_Export_Data $export, array $item ) {
		$translation = $this->get_translation( $item['string'], $export->get_target_language() );
		$ref         = array(
			'object_type'   => PLL_Import_Export::STRINGS_TRANSLATIONS,
			'field_type'    => 'string_translation',
			'object_id'     => 0, // Set 0 for strings so that this parameter is always filled.
			'field_comment' => sprintf( '%s, %s', $item['context'], $item['name'] ),
		);

		// Arrays use Windows line ending syntax. This is also performed in {@see Translation_Entry::key()}.
		$source_string = str_replace( array( "\r\n", "\r" ), "\n", $item['string'] );
		$translation   = str_replace( array( "\r\n", "\r" ), "\n", $translation );

		if ( '' === $source_string ) {
			return;
		}

		$export->add_translation_entry( $ref, $source_string, $translation );
	}

	/**
	 * Returns a translation for the given string, if it exists.
	 *
	 * @since 3.6
	 *
	 * @param string       $item            Source string.
	 * @param PLL_Language $target_language Language to translate into.
	 * @return string
	 */
	private function get_translation( string $item, PLL_Language $target_language ): string {
		if ( ! isset( $this->mo[ $target_language->slug ] ) ) {
			// Cache translations.
			$this->mo[ $target_language->slug ] = new PLL_MO();
			$this->mo[ $target_language->slug ]->import_from_db( $target_language );
		}

		return $this->mo[ $target_language->slug ]->translate( $item );
	}
}
