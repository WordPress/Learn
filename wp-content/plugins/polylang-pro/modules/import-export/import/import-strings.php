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
	 * The success counter.
	 *
	 * @var int
	 */
	protected $success;

	/**
	 * The imported source string.
	 *
	 * @var string[]
	 */
	protected $imported_strings = array();

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

			/** This filter is documented in /polylang/settings/table-string.php */
			$sanitized_translation = apply_filters( 'pll_sanitize_string_translation', $entry->translations[0], $entry->extracted_comments, $entry->context );
			$sanitized_translation = wp_kses_post( $sanitized_translation );

			// Set a unique key for each entry to compare the original and translated strings.
			$key = $entry->key();

			if ( empty( $key ) ) {
				continue;
			}
			if ( isset( $pll_mo_clone->entries[ $key ]->translations[0] ) || isset( $registered_strings[ md5( $key ) ] ) ) {
				// Checks that the string did not exist or has been edited before updating.
				if ( ! isset( $pll_mo_clone->entries[ $key ]->translations[0] ) || $pll_mo_clone->entries[ $key ]->translations[0] !== $sanitized_translation ) {
					$pll_mo->add_entry( $pll_mo->make_entry( $entry->singular, $sanitized_translation ) );
					++$this->success;

					// Store the source strings as ids during the import process.
					$this->imported_strings[] = $entry->singular;
				}
			}
		}

		if ( $this->success ) {
			$pll_mo->export_to_db( $target_language );
		}
	}

	/**
	 * Removes the context for the translation entries.
	 *
	 * @since 3.2
	 * @since 3.3 Moved from PLL_Import_Action to PLL_Import_Strings.
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

	/**
	 * Gets update notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_updated_notice() {
		if ( ! $this->success ) {
			return new WP_Error();
		}

		return new WP_Error(
			'pll_import_strings_success',
			sprintf(
				/* translators: %d is a number of strings translations */
				_n( '%d string translation updated.', '%d string translations updated.', $this->success, 'polylang-pro' ),
				$this->success
			),
			'success'
		);
	}

	/**
	 * Gets warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
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
	public function get_imported_object_ids() {
		return $this->imported_strings;
	}
}
