<?php
/**
 * @package Polylang-Pro
 */

/**
 * PO file, generated from importing translations
 *
 * Handles the reading of a PO file.
 *
 * @since 2.7
 */
class PLL_PO_Import implements PLL_Import_File_Interface {

	/**
	 * Po object.
	 *
	 * @var PO
	 */
	private $po;

	/**
	 * If we have already retrieved the entry or not.
	 *
	 * @var bool
	 */
	private $once;

	/**
	 * Constructor.
	 *
	 * Creates a PO object from an imported file.
	 *
	 * @since 2.7
	 */
	public function __construct() {
		require_once ABSPATH . '/wp-includes/pomo/po.php';
		$this->po = new PO();
	}

	/**
	 * Import the translations from a file.
	 *
	 * Relies on {@see PO::import_from_file()}
	 *
	 * @since 2.7
	 *
	 * @param string $filepath The path on the filesystem where the import file is located.
	 * @return true|WP_Error True on success, a `WP_Error` object if a problem occurs during file import.
	 */
	public function import_from_file( string $filepath ) {
		// PO::import_from_file returns false in case it does not succeed to parse the file.
		if ( ! $this->po->import_from_file( $filepath ) ) {
			return new WP_Error( 'pll_import_invalid_file', __( 'Error: Invalid file.', 'polylang-pro' ) );
		}
		return true;
	}

	/**
	 * Get the target language
	 *
	 * @since 2.7
	 * @since 3.3.1 Change the target language header label. We're now using the official "Language" header
	 *              and add a backward condition to accept the old header.
	 *
	 * @return string|false
	 */
	public function get_target_language() {
		if ( ! empty( $this->po->headers['Language'] ) ) {
			return $this->po->headers['Language'];
		}

		// Backward compatibility with Polylang < 3.3.1.
		if ( ! empty( $this->po->headers['Language-Target'] ) ) {
			return $this->po->headers['Language-Target'];
		}
		return false;
	}

	/**
	 * Get the site reference.
	 *
	 * @since 2.7
	 * @since 3.3.1 Change the site reference header label.
	 *
	 * @return string|false
	 */
	public function get_site_reference() {
		if ( ! empty( $this->po->headers['X-Polylang-Site-Reference'] ) ) {
			return $this->po->headers['X-Polylang-Site-Reference'];
		}

		// Backward compatibility with Polylang < 3.3.1.
		if ( ! empty( $this->po->headers['Site-Reference'] ) ) {
			return $this->po->headers['Site-Reference'];
		}
		return false;
	}

	/**
	 * Returns the reference to the name of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	public function get_generator_name(): string {
		return $this->get_generator()['name'];
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
		return $this->get_generator()['version'];
	}

	/**
	 * Get the next string translation to import.
	 *
	 * @since 2.7
	 *
	 * @return array
	 */
	public function get_next_entry(): array {
		if ( $this->once ) {
			return array();
		}

		$this->once = true;
		return array(
			'id'   => null,
			'type' => PLL_Import_Export::STRINGS_TRANSLATIONS,
			'data' => $this->po,
		);
	}

	/**
	 * Returns the reference to the application that generated the file (name + version).
	 *
	 * @since 3.3
	 *
	 * @return array {
	 *     An array containing the application's name and version.
	 *
	 *     @type string $name    The application's name.
	 *     @type string $version The application's version.
	 * }
	 *
	 * @phpstan-return array{name:string,version:string}
	 */
	private function get_generator() {
		if ( empty( $this->po->headers['Project-Id-Version'] ) || ! is_string( $this->po->headers['Project-Id-Version'] ) ) {
			return array(
				'name'    => '',
				'version' => '',
			);
		}

		$generator       = explode( '/', trim( $this->po->headers['Project-Id-Version'], '/ ' ) );
		$product_version = isset( $generator[1] ) ? trim( array_pop( $generator ) ) : '';
		$product_name    = trim( implode( '/', $generator ) );
		$product_name    = 'POLYLANG_EXPORT' === $product_name ? PLL_Import_Export::APP_NAME : $product_name; // Backward compatibility with Polylang Pro < 3.3.

		return array(
			'name'    => $product_name,
			'version' => $product_version,
		);
	}
}
