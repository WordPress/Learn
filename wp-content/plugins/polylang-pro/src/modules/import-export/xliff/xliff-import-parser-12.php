<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.1
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Handles Xliff 1.2 file import.
 *
 * @since 3.1
 *
 * This class uses PHP built in DOMDocument.
 *
 * @link https://www.php.net/manual/en/book.dom.php
 * @uses libxml
 */
class PLL_Xliff_Import_Parser_12 extends PLL_Xliff_Import_Parser_Base {
	/**
	 * Returns the site reference.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	public function get_site_reference(): string {
		$site_reference_list = $this->xpath->query( '//ns:file/@original' );
		if ( ! $site_reference_list ) {
			return '';
		}

		$site_reference_item = $site_reference_list->item( 0 );
		if ( ! $site_reference_item ) {
			return '';
		}

		$site_reference = $site_reference_item->nodeValue;
		if ( ! $site_reference ) {
			return '';
		}

		// Backward compatibility with Polylang Pro < 3.3.
		$compat_reference = preg_replace( '/^\s*polylang\|/', '', $site_reference );

		return is_string( $compat_reference ) ? $compat_reference : $site_reference;
	}

	/**
	 * Returns the reference to the name of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	public function get_generator_name(): string {
		$product_names = $this->xpath->query( '//ns:file/@product-name' );

		if ( empty( $product_names ) ) {
			return $this->get_compat_generator_name();
		}

		$product_name = $product_names->item( 0 );

		if ( empty( $product_name ) ) {
			return $this->get_compat_generator_name();
		}

		$product_name = $product_name->nodeValue;
		$product_name = is_string( $product_name ) ? trim( $product_name ) : '';

		if ( empty( $product_name ) ) {
			return $this->get_compat_generator_name();
		}

		return $product_name;
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
		$product_versions = $this->xpath->query( '//ns:file/@product-version' );

		if ( empty( $product_versions ) ) {
			return '';
		}

		$product_version = $product_versions->item( 0 );

		if ( empty( $product_version ) ) {
			return '';
		}

		return is_string( $product_version->nodeValue ) ? trim( $product_version->nodeValue ) : '';
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
		return 'urn:oasis:names:tc:xliff:document:1.2';
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
		return sprintf( '//ns:group[@restype="x-%s"]', $type );
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
		return 'resname';
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
		return PLL_Xliff_Export_12::EXTRA_DATA_PROP_NAME;
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
		return 'trans-unit';
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
		return '//ns:file/@target-language';
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
		$restype = trim( $trans_unit->getAttribute( 'restype' ), 'x-' );
		$context = array( Context::FIELD => $restype );

		/*
		 * Backward compatibility with Polylang Pro < 3.6.
		 *
		 * The `resname` attribute used to identify blocks in the post content is no longer used.
		 * So, we need to exclude it from the context when the post content was exported with a version < 3.6
		 * to be able to match with the source content where blocks are no longer identified.
		 */
		if ( $trans_unit->hasAttribute( 'resname' ) && PLL_Import_Export::POST_CONTENT !== $restype ) {
			$context[ Context::ID ] = $trans_unit->getAttribute( 'resname' );
		}
		$entry = array( 'context' => Context::to_string( $context ) );

		return array_merge( $entry, $this->get_singular_and_translations_for_entry( $trans_unit ) );
	}

	/**
	 * Returns the reference to the name of the application that generated the file (back compatibility).
	 * Before PLL Pro 3.3, the `original` attribute was storing "polylang|{site_url}".
	 *
	 * @since 3.3
	 *
	 * @return string 'Polylang' or an empty string.
	 */
	private function get_compat_generator_name(): string {
		$site_references = $this->xpath->query( '//ns:file/@original' );

		if ( empty( $site_references ) ) {
			return '';
		}

		$site_reference = $site_references->item( 0 );

		if ( empty( $site_reference ) ) {
			return '';
		}

		$site_reference = $site_reference->nodeValue;
		$site_reference = is_string( $site_reference ) ? trim( $site_reference ) : '';

		if ( empty( $site_reference ) || ! preg_match( '/^\s*polylang\|/', $site_reference ) ) {
			return '';
		}

		return PLL_Import_Export::APP_NAME;
	}
}
