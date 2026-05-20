<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.6
 */

/**
 * Class for Xliff import.
 *
 * @since 3.6
 *
 * This class uses PHP built in DOMDocument.
 *
 * @link https://www.php.net/manual/en/book.dom.php
 * @uses libxml
 */
class PLL_Xliff_Import implements PLL_Import_File_Interface {

	/**
	 * @var string[]
	 */
	const SUPPORTED_VERSIONS_1 = array( '1.0', '1.1', '1.2' );

	/**
	 * @var string[]
	 */
	const SUPPORTED_VERSION_2 = array( '2.0', '2.1' );

	/**
	 * The xliff parser.
	 *
	 * @var PLL_Xliff_Import_Parser_Base|null
	 */
	protected $parser;

	/**
	 * Imports translations from a file.
	 *
	 * @since 3.1
	 *
	 * @param string $filepath The path on the filesystem where the import file is located.
	 * @return true|WP_Error True on success, a `WP_Error` object if a problem occurs during file import.
	 */
	public function import_from_file( string $filepath ) {
		if ( ! extension_loaded( 'libxml' ) ) {
			return new WP_Error(
				'pll_libxml_missing',
				__( 'Your PHP installation appears to be missing the libxml extension which is required by the importer.', 'polylang-pro' )
			);
		}

		$file_contents = file_get_contents( $filepath );
		if ( false === $file_contents ) {
			return new WP_Error(
				'pll_import_file_contents_error',
				__( 'Something went wrong during the file import.', 'polylang-pro' )
			);
		}

		$parser = $this->get_parser( $file_contents );

		if ( is_wp_error( $parser ) ) {
			return $parser;
		}

		$this->parser = $parser;

		$document = PLL_DOM_Document::from_xml( $file_contents );
		if ( $document->has_errors() || ! $this->parser->parse_xml( $document ) ) {
			return new WP_Error(
				'pll_import_xml_error',
				__( 'An error occurred during the import, please make sure your file is correctly formatted.', 'polylang-pro' )
			);
		}

		return true;
	}

	/**
	 * Gets the next term, post or string translations to import.
	 *
	 * @since 3.1
	 *
	 * @return array {
	 *     string       $type Either 'post', 'term' or 'string_translations'
	 *     int          $id   ID of the object in the database (if applicable)
	 *     Translations $data Objects holding all the retrieved Translations
	 * }
	 */
	public function get_next_entry(): array {
		if ( empty( $this->parser ) ) {
			return array();
		}
		return $this->parser->get_next_entry();
	}

	/**
	 * Gets target language.
	 *
	 * @since 3.1
	 *
	 * @return string|false
	 */
	public function get_target_language() {
		if ( empty( $this->parser ) ) {
			return '';
		}
		return $this->parser->get_target_language();
	}

	/**
	 * Gets site reference.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get_site_reference(): string {
		if ( empty( $this->parser ) ) {
			return '';
		}
		return $this->parser->get_site_reference();
	}

	/**
	 * Returns the reference to the name of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	public function get_generator_name(): string {
		if ( empty( $this->parser ) ) {
			return '';
		}
		return $this->parser->get_generator_name();
	}

	/**
	 * Returns the reference to the version of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application version. An empty string if it couldn't be found or the name of the application.
	 *                couldn't be found.
	 */
	public function get_generator_version(): string {
		if ( empty( $this->parser ) ) {
			return '';
		}
		return $this->parser->get_generator_version();
	}

	/**
	 * Returns the xliff version read in the uploaded file.
	 *
	 * @since 3.6
	 *
	 * @param string $content Full xliff content.
	 * @return string Xliff version gotten from the xliff content.
	 */
	private function get_xliff_version( string $content ): string {
		// Get the xliff version in the beginning of the uploaded file to choose which parser to use.
		preg_match( '@<xliff\s(?:[^>]*\s)?version\s*=\s*"\s*(\d+(?:.\d+)?)\s*"@', substr( $content, 0, 1024 ), $matched_version );

		return $matched_version[1] ?? '';
	}

	/**
	 * Returns the xliff parser depending on the xliff version.
	 *
	 * @since 3.6
	 *
	 * @param string $content Full xliff content.
	 * @return PLL_Xliff_Import_Parser_Base|WP_Error Xliff major version.
	 */
	private function get_parser( string $content ) {
		$version = $this->get_xliff_version( $content );

		if ( in_array( $version, self::SUPPORTED_VERSIONS_1, true ) ) {
			return new PLL_Xliff_Import_Parser_12();
		}

		if ( in_array( $version, self::SUPPORTED_VERSION_2, true ) ) {
			return new PLL_Xliff_Import_Parser_21();
		}

		return new WP_Error(
			'pll_import_unsupported_xliff_version',
			/* translators: %s is a list of versions. */
			__( 'The xliff version is not supported.', 'polylang-pro' )
		);
	}
}
