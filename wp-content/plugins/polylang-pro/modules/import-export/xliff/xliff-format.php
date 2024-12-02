<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Xliff_Format
 *
 * @since 3.1
 *
 * Manages support of XLIFF file format 2.1
 */
class PLL_Xliff_Format extends PLL_File_Format {
	/**
	 * @var string
	 */
	public $extension = 'xliff';

	/**
	 * @var string[]
	 */
	public $mime_type = array( 'xlf|xliff' => 'text/xml' );

	/**
	 * PLL_Xliff_Format constructor.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		// MIME type does not use the same string for PHP versions < 7.2.
		if ( version_compare( phpversion(), '7.2', '<' ) ) {
			$this->mime_type = array( 'xlf|xliff' => 'application/xml' );
		}
	}

	/**
	 * Whether the xliff format is supported or not by the current environment.
	 *
	 * @since 3.1
	 *
	 * @return true|WP_Error
	 */
	public function is_supported() {
		if ( ! extension_loaded( 'libxml' ) ) {
			return new WP_Error( 'pll_libxml_missing', __( 'Your PHP installation appears to be missing the libxml extension which is required by the importer.', 'polylang-pro' ) );
		}

		return true;
	}

	/**
	 * Returns the associated import class.
	 *
	 * @since 3.1
	 *
	 * @return PLL_Xliff_Import
	 */
	public function get_import() {
		return new PLL_Xliff_Import();
	}

	/**
	 * Returns the associated export class.
	 *
	 * @since 3.6
	 *
	 * @param string $version Optional file format version.
	 * @return string
	 *
	 * @phpstan-return class-string<PLL_Xliff_Export_12>|class-string<PLL_Xliff_Export_20>|class-string<PLL_Xliff_Export_21>
	 */
	public function get_export_class( $version = '' ): string {
		switch ( $version ) {
			case '20':
				$class_name = PLL_Xliff_Export_20::class;
				break;
			case '21':
				$class_name = PLL_Xliff_Export_21::class;
				break;
			default: // 1.2
				$class_name = PLL_Xliff_Export_12::class;
				break;
		}
		return $class_name;
	}
}
