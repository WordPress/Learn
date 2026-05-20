<?php
/**
 * @package Polylang-Pro
 */

/**
 * Interface PLL_Import_File_Interface
 *
 * Each class implementing this interface shall be the representation of a single file to be imported
 *
 * @since 3.2
 */
interface PLL_Import_File_Interface {
	/**
	 * Import the translations from a file.
	 *
	 * @since 2.7
	 *
	 * @param string $filepath The path on the filesystem where the import file is located.
	 * @return true|WP_Error True on success, a `WP_Error` object if a problem occurs during file import.
	 */
	public function import_from_file( string $filepath );

	/**
	 * Get the target language
	 *
	 * @since 2.7
	 *
	 * @return string|false
	 */
	public function get_target_language();

	/**
	 * Get the next content to import.
	 *
	 * @since 2.7
	 *
	 * @return array
	 */
	public function get_next_entry(): array;

	/**
	 * Get the site reference
	 *
	 * @since 2.7
	 *
	 * @return string|false
	 */
	public function get_site_reference();

	/**
	 * Returns the name of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	public function get_generator_name(): string;

	/**
	 * Returns the version of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application version. An empty string if it couldn't be found or the name of the application.
	 *                couldn't be found.
	 */
	public function get_generator_version(): string;
}
