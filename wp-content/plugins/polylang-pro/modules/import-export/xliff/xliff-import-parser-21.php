<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.6
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Handles Xliff 2.1 file import.
 *
 * @since 3.6
 *
 * This class uses PHP built in DOMDocument.
 *
 * @link https://www.php.net/manual/en/book.dom.php
 * @uses libxml
 */
class PLL_Xliff_Import_Parser_21 extends PLL_Xliff_Import_Parser_Base {
	/**
	 * Returns the site reference.
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	public function get_site_reference(): string {
		$original = $this->get_data_from_original_attribute();

		return ! empty( $original[2] ) ? $original[2] : '';
	}

	/**
	 * Returns the reference to the name of the application that generated the file.
	 *
	 * @since 3.6
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	public function get_generator_name(): string {
		$original = $this->get_data_from_original_attribute();

		return ! empty( $original[0] ) && is_string( $original[0] ) ? trim( $original[0] ) : '';
	}

	/**
	 * Returns the reference to the version of the application that generated the file.
	 *
	 * @since 3.6
	 *
	 * @return string The application version. An empty string if it couldn't be found or the name of the application.
	 *                couldn't be found.
	 */
	public function get_generator_version(): string {
		$original = $this->get_data_from_original_attribute();

		return ! empty( $original[1] ) && is_string( $original[1] ) ? trim( $original[1] ) : '';
	}

	/**
	 * Returns the XML namespace.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_xml_namespace(): string {
		return 'urn:oasis:names:tc:xliff:document:2.0';
	}

	/**
	 * Returns the Xpath to the translation entry's type attribute.
	 *
	 * @since 3.6
	 *
	 * @param string $type The type of content to import.
	 * @return string
	 */
	protected function get_translation_entry_type_xpath( string $type ): string {
		return sprintf( '//ns:group[@type="x:%s"]', $type );
	}

	/**
	 * Returns the name of the XML attribute used to store the translation entry's ID.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_translation_entry_id_xml_attr_name(): string {
		return 'name';
	}

	/**
	 * Returns the name of the XML attribute used to store custom data.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_data_xml_attr_name(): string {
		return PLL_Xliff_Export_21::EXTRA_DATA_PROP_NAME;
	}

	/**
	 * Returns the name of the "unit" tag.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_unit_tag_name(): string {
		return 'unit';
	}

	/**
	 * Returns the Xpath to the target language.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_target_language_xpath(): string {
		return '//ns:xliff/@trgLang';
	}

	/**
	 * Creates an entry and fills it from trans unit.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement $trans_unit A trans unit node.
	 * @return array {
	 *     string $context      The context.
	 *     string $id           The optional ID.
	 *     string $singular     The singular.
	 *     string $translations The translations.
	 * }
	 */
	protected function populate_entry_from_trans_unit( DOMElement $trans_unit ): array {
		$context = array( Context::FIELD => trim( $trans_unit->getAttribute( 'type' ), 'x:' ) );
		if ( $trans_unit->hasAttribute( 'name' ) ) {
			$context[ Context::ID ] = $trans_unit->getAttribute( 'name' );
		}
		$entry = array( 'context' => Context::to_string( $context ) );

		$segment = $trans_unit->getElementsByTagName( 'segment' )->item( 0 );
		if ( ! $segment instanceof DOMElement || empty( $segment->nodeValue ) ) {
			return $entry;
		}

		return array_merge( $entry, $this->get_singular_and_translations_for_entry( $segment ) );
	}

	/**
	 * Returns data from the original attribute.
	 *
	 * @since 3.6
	 *
	 * @return array {
	 *      An array containing the file origin data.
	 *
	 *     @type string Generator name.
	 *     @type string Generator version.
	 *     @type string Site reference.
	 * }
	 */
	private function get_data_from_original_attribute(): array {
		$original_list = $this->xpath->query( '//ns:file/@original' );
		if ( ! $original_list ) {
			return array();
		}

		$original_item = $original_list->item( 0 );
		if ( ! $original_item ) {
			return array();
		}

		$original = $original_item->nodeValue;
		if ( ! $original ) {
			return array();
		}

		$original = explode( '|', $original );
		return count( $original ) === 3 ? $original : array();
	}
}
