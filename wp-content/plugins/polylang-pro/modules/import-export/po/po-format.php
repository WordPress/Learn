<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_PO_Format
 *
 * @since 3.1
 *
 * Manages the support for Po format for Polylang Import / Export feature.
 */
class PLL_PO_Format extends PLL_File_Format {
	/**
	 * @var string
	 */
	public $extension = 'po';

	/**
	 * @var string[]
	 */
	public $mime_type = array( 'po' => 'text/x-po' );

	/**
	 * Po format is always supported.
	 *
	 * @since 3.1
	 *
	 * @return true
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * Returns the associated import class.
	 *
	 * @since 3.1
	 *
	 * @return PLL_PO_Import
	 */
	public function get_import() {
		return new PLL_PO_Import();
	}

	/**
	 * Returns the associated export class.
	 *
	 * @since 3.6
	 *
	 * @param string $version Optional file format version. Not used for PO.
	 * @return string
	 *
	 * @phpstan-return class-string<PLL_PO_Export>
	 */
	public function get_export_class( $version = '' ): string { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return PLL_PO_Export::class;
	}
}
