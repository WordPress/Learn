<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.6
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Abstract class for Xliff import.
 *
 * @since 3.6
 *
 * This class uses PHP built in DOMDocument.
 *
 * @link https://www.php.net/manual/en/book.dom.php
 * @uses libxml
 */
abstract class PLL_Xliff_Import_Parser_Base {
	/**
	 * The Xpath object.
	 *
	 * @var DOMXPath The Xpath object.
	 */
	protected $xpath;

	/**
	 * An array of XLIFF iterators.
	 *
	 * @var PLL_DOM_Nodes_Iterator[]
	 */
	protected $iterators = array();

	/**
	 * Parses an XML response body.
	 *
	 * @since 3.1
	 *
	 * @param DOMDocument $document An HTML document parsed by PHP DOMDocument.
	 * @return bool True if no problem occurs during the parsing, false otherwise.
	 */
	public function parse_xml( DOMDocument $document ): bool {
		$this->xpath = new DOMXPath( $document );
		$this->xpath->registerNamespace( 'ns', $this->get_xml_namespace() );

		$types = array(
			PLL_Import_Export::TYPE_TERM,
			PLL_Import_Export::TYPE_POST,
			PLL_Import_Export::STRINGS_TRANSLATIONS,
		);

		foreach ( $types as $type ) {
			$item = $this->xpath->query( $this->get_translation_entry_type_xpath( $type ) );
			if ( ! empty( $item ) && $item->length ) {
				$this->iterators[ $type ] = new PLL_DOM_Nodes_Iterator( $item );
			}
		}

		return (bool) array_filter( $this->iterators );
	}

	/**
	 * Returns the next term, post or string translation to import.
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
		if ( empty( $this->iterators ) ) {
			return array();
		}

		foreach ( $this->iterators as $iterator_type => $iterator ) {
			if ( $iterator->valid() ) {
				$item = $iterator->current();
				if ( empty( $item ) ) {
					return array();
				}

				$iterator->next();
				return $this->create_translation_entry( $item, $iterator_type );
			}
		}

		return array();
	}

	/**
	 * Creates the `Translations` object.
	 * And then returns it in an array with additional data.
	 *
	 * @since 3.3
	 *
	 * @param DOMNode $item The current element.
	 * @param string  $type The object type.
	 * @return array {
	 *     string       $type Either 'post', 'term' or 'string_translations'
	 *     int          $id   ID of the object in the database (if applicable)
	 *     Translations $data Objects holding all the retrieved Translations
	 * }
	 */
	protected function create_translation_entry( DOMNode $item, string $type ): array {
		$translations = new Translations();

		if ( ! $item instanceof DOMElement ) {
			return array();
		}

		$this->add_child_units( $item, $translations );

		return array(
			'type' => $type,
			'id'   => (int) $item->getAttribute( $this->get_translation_entry_id_xml_attr_name() ),
			'data' => $translations,
		);
	}

	/**
	 * Returns an entry array with singular and translations from node.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement $node  The current node.
	 * @return array {
	 *     string $singular     The singular.
	 *     string $translations The translations.
	 * }
	 */
	protected function get_singular_and_translations_for_entry( DOMElement $node ): array {
		$entry = array();

		if ( $node->getElementsByTagName( 'source' )->item( 0 ) instanceof DOMElement ) {
			$entry['singular'] = $node->getElementsByTagName( 'source' )->item( 0 )->nodeValue;
		}

		if ( $node->getElementsByTagName( 'target' )->item( 0 ) instanceof DOMElement ) {
			$entry['translations'] = array( $node->getElementsByTagName( 'target' )->item( 0 )->nodeValue );
		}

		return $entry;
	}

	/**
	 * Returns the target language.
	 *
	 * @since 3.1
	 *
	 * @return string|false
	 */
	public function get_target_language() {
		$target_lang_list = $this->xpath->query( $this->get_target_language_xpath() );
		if ( ! $target_lang_list ) {
			return false;
		}

		$target_lang = $target_lang_list->item( 0 );
		if ( ! $target_lang ) {
			return false;
		}

		if ( ! $target_lang->nodeValue ) {
			return false;
		}
		return $target_lang->nodeValue;
	}

	/**
	 * Returns the site reference.
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	abstract public function get_site_reference(): string;

	/**
	 * Returns the reference to the name of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application name. An empty string if it couldn't be found.
	 */
	abstract public function get_generator_name(): string;

	/**
	 * Returns the reference to the version of the application that generated the file.
	 *
	 * @since 3.3
	 *
	 * @return string The application version. An empty string if it couldn't be found or the name of the application.
	 *                couldn't be found.
	 */
	abstract public function get_generator_version(): string;

	/**
	 * Returns the XML namespace.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_xml_namespace(): string;

	/**
	 * Returns the Xpath to the translation entry's type attribute.
	 *
	 * @since 3.6
	 *
	 * @param string $type The type of content to import.
	 * @return string
	 */
	abstract protected function get_translation_entry_type_xpath( string $type ): string;

	/**
	 * Returns the name of the XML attribute used to store the translation entry's ID.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_translation_entry_id_xml_attr_name(): string;

	/**
	 * Returns the name of the XML attribute used to store custom data.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_data_xml_attr_name(): string;

	/**
	 * Returns the name of the "unit" tag.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_unit_tag_name(): string;

	/**
	 * Returns the Xpath to the target language.
	 *
	 * @since 3.6
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_target_language_xpath(): string;

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
	abstract protected function populate_entry_from_trans_unit( DOMElement $trans_unit ): array;

	/**
	 * Adds translation entries from the XML tree to the given translation entry object.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement   $item         A node that may contain `trans-unit` and `group` child nodes.
	 * @param Translations $translations A translation entry object.
	 * @param array        $args         {
	 *     Optional arguments.
	 *
	 *     @type string $encoding Encoding format for all of the `trans-unit` direct child nodes.
	 * }
	 * @return void
	 */
	private function add_child_units( DOMElement $item, Translations $translations, array $args = array() ) {
		foreach ( $item->childNodes as $trans_unit ) {
			if ( ! $trans_unit instanceof DOMElement ) {
				continue;
			}

			if ( 'group' === $trans_unit->nodeName ) {
				// Encoding group.
				$extradata = sprintf(
					';%s;', // `;` is used as separator in case we need to store more data.
					$trans_unit->getAttribute( $this->get_data_xml_attr_name() )
				);

				if ( ! preg_match( '/;\s*encoding:\s*(?<encoding>[^\s]+)\s*;/', $extradata, $matches ) ) {
					continue;
				}

				$this->add_child_units(
					$trans_unit,
					$translations,
					array_merge( $args, array( 'encoding' => $matches['encoding'] ) )
				);
				continue;
			}

			if ( $this->get_unit_tag_name() !== $trans_unit->nodeName ) {
				continue;
			}

			$entry = $this->populate_entry_from_trans_unit( $trans_unit );

			if ( ! empty( $args['encoding'] ) ) {
				$entry['context'] = Context::to_string(
					array_merge(
						Context::to_array( $entry['context'] ),
						array( Context::ENCODING => $args['encoding'] )
					)
				);
			}

			$translations->add_entry( $entry );
		}
	}
}
