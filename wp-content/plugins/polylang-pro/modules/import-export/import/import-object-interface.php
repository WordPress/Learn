<?php
/**
 * @package Polylang-Pro
 */

/**
 * Interface for the import strategy.
 *
 * @since 3.3
 */
interface PLL_Import_Object_Interface {

	/**
	 * Handles the import of a content type.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry {
	 *   An array containing the translations data.
	 *
	 *   @type string       $type Either 'post', 'term' or 'string_translations'.
	 *   @type int          $id   Id of the object in the database (if applicable).
	 *   @type Translations $data Objects holding all the retrieved Translations.
	 * }
	 * @param PLL_Language $target_language The targeted language for import.
	 * @return void
	 */
	public function translate( $entry, $target_language );

	/**
	 * Get update notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_updated_notice();

	/**
	 * Get warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice();

	/**
	 * Returns the object type.
	 *
	 * @since 3.3
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Returns the imported object ids.
	 *
	 * @since 3.3
	 *
	 * @return array
	 */
	public function get_imported_object_ids();
}
