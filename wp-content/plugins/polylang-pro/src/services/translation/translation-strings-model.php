<?php
/**
 * @package Polyang-Pro
 */

/**
 * Class to manage translated strings import.
 *
 * @since 3.7
 */
class PLL_Translation_Strings_Model implements PLL_Translation_Data_Model_Interface {
	/**
	 * Imported strings.
	 *
	 * @var string[]
	 */
	private $imported_strings = array();

	/**
	 * Handles the import of strings translations.
	 *
	 * @since 3.3
	 * @since 3.7 Moved from PLL_Import_Strings to PLL_Translation_Strings_Model.
	 *
	 * @param array        $entry {
	 *     An array containing the translations data.
	 *     @type string       $type Either 'post', 'term' or 'string_translations'.
	 *     @type int          $id   Id of the object in the database (if applicable).
	 *     @type Translations $data Objects holding all the retrieved Translation_Entry objects.
	 * }
	 * @param PLL_Language $target_language The targeted language for import.
	 * @return string[]|WP_Error The imported strings, `WP_Error` on failure.
	 */
	public function translate( array $entry, PLL_Language $target_language ) {
		$this->imported_strings = array(); // Reset the imported strings array.

		$pll_mo = new PLL_MO();
		$pll_mo->import_from_db( $target_language );
		$registered_strings = PLL_Admin_Strings::get_strings();

		$translations = $entry['data'];

		// Clone the $pll_mo element to avoid modifying the original one since we will then update it.
		$pll_mo_clone = clone $pll_mo;

		// Remove the context for the translation entries to generate the same key between the translation strings
		// and the database strings.
		$translations->entries = $this->remove_context_from_translations( $translations->entries );

		foreach ( $translations->entries as $entry ) {
			if ( empty( $entry->translations ) ) {
				$entry->translations = array( '' );
			}

			/** This filter is documented in /polylang/src/settings/table-string.php */
			$sanitized_translation = apply_filters( 'pll_sanitize_string_translation', $entry->translations[0], $entry->extracted_comments, $entry->context, $entry->singular, $pll_mo->translate_if_any( $entry->singular ) );
			$sanitized_translation = wp_kses_post( $sanitized_translation );

			if ( '' === $sanitized_translation ) {
				// Don't overwrite a translation with an empty string.
				continue;
			}

			// Set a unique key for each entry to compare the original and translated strings.
			$key = $entry->key();

			if ( empty( $key ) ) {
				continue;
			}
			if ( isset( $pll_mo_clone->entries[ $key ]->translations[0] ) || isset( $registered_strings[ md5( $key ) ] ) ) {
				// Checks that the string did not exist or has been edited before updating.
				if ( ! isset( $pll_mo_clone->entries[ $key ]->translations[0] ) || $pll_mo_clone->entries[ $key ]->translations[0] !== $sanitized_translation ) {
					$pll_mo->add_entry( $pll_mo->make_entry( $entry->singular, $sanitized_translation ) );
					// Store the source strings as ids during the import process.
					$this->imported_strings[] = $entry->singular;
				}
			}
		}

		if ( 0 < count( $this->imported_strings ) ) {
			$pll_mo->export_to_db( $target_language );

			return $this->imported_strings;
		}

		return new WP_Error( 'pll_translate_strings_no_imported_strings', __( 'No strings have been translated.', 'polylang-pro' ) );
	}

	/**
	 * Performs actions after a translation process.
	 * Does nothing.
	 *
	 * @since 3.7
	 *
	 * @param string[]     $ids             The entity ids to process after translation.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function do_after_process( array $ids, PLL_Language $target_language ) { //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Nothing to do.
	}

	/**
	 * Removes the context for the translation entries.
	 *
	 * @since 3.2
	 * @since 3.3 Moved from PLL_Import_Action to PLL_Import_Strings.
	 * @since 3.7 Moved from PLL_Import_Strings to PLL_Translation_Strings_Model.
	 *
	 * @param  Translation_Entry[] $translations An array with all the entries.
	 * @return Translation_Entry[]               An array with the same entries with an empty context.
	 */
	private function remove_context_from_translations( $translations ) {
		foreach ( $translations as $translation_entry ) {
			$translation_entry->context = '';
		}
		return $translations;
	}
}
