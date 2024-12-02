<?php
/**
 * @package Polylang-Pro
 */

/**
 * Abstract class to use to export data as a file.
 *
 * @since 3.1
 * @since 3.6 Extends `PLL_Export_Data` instead of implementing `PLL_Export_File_Interface`.
 */
abstract class PLL_Export_File extends PLL_Export_Data {
	/**
	 * Returns the name of the file to export.
	 *
	 * @since 2.7
	 *
	 * @return string
	 */
	public function get_filename(): string {
		$source_language = $this->get_source_language()->get_locale( 'display' );
		$target_language = $this->get_target_language()->get_locale( 'display' );
		$datenow = gmdate( 'Y-m-d_G-i-s' );
		$extension = $this->get_extension();

		return "{$source_language}_{$target_language}_{$datenow}.{$extension}";
	}

	/**
	 * Returns exported data.
	 * Backward compatibility with PHP < 7.2 where abstract parent method cannot be overloaded by an abstract child method.
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	public function get(): string {
		return '';
	}

	/**
	 * Returns the current file extension.
	 *
	 * @since 3.1
	 *
	 * @return string The file extension.
	 */
	abstract protected function get_extension(): string;
}
