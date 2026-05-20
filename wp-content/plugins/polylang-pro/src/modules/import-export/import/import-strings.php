<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle strings translations import.
 *
 * @since 3.3
 */
class PLL_Import_Strings implements PLL_Import_Object_Interface {
	/**
	 * Handles the translation of strings.
	 *
	 * @var PLL_Translation_Strings_Model
	 */
	private $translation_model;

	/**
	 * Imported strings, `null` if no process has been done.
	 *
	 * @var string[]|null
	 */
	private $imported_strings;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Translation_Strings_Model $translation_model The object to handle strings translations.
	 */
	public function __construct( PLL_Translation_Strings_Model $translation_model ) {
		$this->translation_model = $translation_model;
	}

	/**
	 * Handles the import of strings translations.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry {
	 *     An array containing the translations data.
	 *     @type string       $type Either 'post', 'term' or 'string_translations'.
	 *     @type int          $id   Id of the object in the database (if applicable).
	 *     @type Translations $data Objects holding all the retrieved Translation_Entry objects.
	 * }
	 * @param PLL_Language $target_language The targeted language for import.
	 */
	public function translate( $entry, $target_language ) {
		$this->imported_strings = array();
		$result = $this->translation_model->translate( $entry, $target_language );
		if ( ! is_wp_error( $result ) ) {
			$this->imported_strings = $result;
		}
	}

	/**
	 * Performs actions after an import process.
	 *
	 * @since 3.7
	 *
	 * @param string[]     $ids             The entity ids to process after import.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function do_after_import_process( array $ids, PLL_Language $target_language ) {
		$this->translation_model->do_after_process( $ids, $target_language );
	}

	/**
	 * Returns update notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_updated_notice() {
		if ( ! isset( $this->imported_strings ) || 0 === count( $this->imported_strings ) ) {
			return new WP_Error();
		}

		return new WP_Error(
			'pll_import_strings_success',
			sprintf(
				/* translators: %d is a number of strings translations */
				_n( '%d string translation updated.', '%d string translations updated.', count( $this->imported_strings ), 'polylang-pro' ),
				count( $this->imported_strings )
			),
			'success'
		);
	}

	/**
	 * Returns warning notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
		if ( isset( $this->imported_strings ) && 0 === count( $this->imported_strings ) ) {
			return new WP_Error(
				'pll_import_strings_nothing_imported',
				__( 'No string translations updated. Please check that the original strings in your file match those on the site.', 'polylang-pro' ),
				'warning'
			);
		}

		return new WP_Error();
	}

	/**
	 * Returns the object type.
	 *
	 * @since 3.3
	 *
	 * @return string
	 */
	public function get_type() {
		return PLL_Import_Export::STRINGS_TRANSLATIONS;
	}

	/**
	 * Returns the imported source strings as ids.
	 *
	 * @since 3.3
	 *
	 * @return string[]
	 */
	public function get_imported_object_ids(): array {
		return (array) $this->imported_strings;
	}
}
