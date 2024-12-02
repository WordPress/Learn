<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.6
 */

/**
 * Abstract class for Xliff export.
 *
 * @phpstan-import-type translationEntryRef from PLL_Export_Data
 *
 * @since 3.6
 */
abstract class PLL_Xliff_Export_Base extends PLL_Export_File {
	/**
	 * The root element of the XML tree.
	 *
	 * @var DOMDocument
	 */
	protected $document;

	/**
	 * The element in the XML tree that holds the groups.
	 *
	 * @var DOMElement
	 */
	protected $groups_wrapper;

	/**
	 * This represents the different sources that can be added into an export.
	 *
	 * @var DOMElement[]
	 *
	 * @phpstan-var array<non-falsy-string, DOMElement>
	 */
	protected $groups = array();

	/**
	 * Number of the translations units.
	 *
	 * @var int
	 *
	 * @phpstan-var int<0, max>
	 */
	protected $units_count = 0;

	/**
	 * Stores the type of encoding for each group. This ensures to have the same format for all fields of a meta.
	 *
	 * @var string[] Meta name as array keys, encoding format as array values.
	 */
	private $encodings = array();

	/**
	 * Constructor.
	 * Declares xml version and creates the root element for the document.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $source_language The source language of the exported data.
	 * @param PLL_Language $target_language The target language of the exported data.
	 */
	public function __construct( PLL_Language $source_language, PLL_Language $target_language ) {
		parent::__construct( $source_language, $target_language );

		$this->document = new DOMDocument( '1.0', 'UTF-8' );
	}

	/**
	 * Adds a source string to exported data and optionally a pre-existing translated one.
	 *
	 * @since 3.6
	 *
	 * @param array  $ref    {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @param string $source The source to be translated.
	 * @param string $target Optional, a preexisting translation, if any.
	 * @return void
	 *
	 * @phpstan-param translationEntryRef $ref
	 * @phpstan-param non-empty-string $source
	 */
	public function add_translation_entry( array $ref, string $source, string $target = '' ) {
		if ( ! $this->are_entry_parameters_valid( $ref, $source ) ) {
			return;
		}

		$this->add_valid_translation_entry( $ref, $source, $target );
	}

	/**
	 * Returns exported data.
	 *
	 * @since 3.6
	 *
	 * @return string An XML formatted string.
	 */
	public function get(): string {
		$this->document->preserveWhiteSpace = false;
		$this->document->formatOutput       = true;
		return (string) $this->document->saveXML( null, LIBXML_NOEMPTYTAG );
	}

	/**
	 * Helper function to insert new elements in our DOMDocument.
	 *
	 * @since 3.6
	 *
	 * @see https://www.php.net/manual/fr/domdocument.createcdatasection.php
	 *
	 * @param DOMNode  $parent     Could be a DOMDocument or a DOMElement.
	 * @param string   $tag_name   Name of the attribute to set.
	 * @param string[] $attributes Optional attributes. Attribute names as array keys, attribute values as array values.
	 * @param string   $content    Optional. Could specify some text content to insert into the new node.
	 *                             /!\ This works only for text content, CDATA section has to be created with
	 *                             `DOMDocument::createCDATASection()` and appended.
	 * @return DOMElement          The newly created `DOMElement`.
	 *
	 * @phpstan-param non-falsy-string $tag_name
	 * @phpstan-param array<non-falsy-string, string> $attributes
	 */
	protected function add_child_element( DOMNode $parent, string $tag_name, array $attributes = array(), string $content = '' ): DOMElement {
		$new_element = $this->document->createElement( $tag_name, $content );

		foreach ( $attributes as $name => $value ) {
			$new_element->setAttribute( $name, $value );
		}

		$parent->appendChild( $new_element );

		return $new_element;
	}

	/**
	 * Returns the name of the current translation group.
	 * Reuses an existing group or creates a new one.
	 *
	 * @since 3.6
	 *
	 * @param array $ref {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @return string
	 *
	 * @phpstan-param translationEntryRef $ref
	 */
	protected function get_source_reference( array $ref ): string {
		$group_name = $ref['object_type'];

		if ( ! empty( $ref['object_id'] ) ) {
			$group_name .= '_' . $ref['object_id'];
		}

		if ( ! array_key_exists( $group_name, $this->groups ) ) {
			$this->groups[ $group_name ] = $this->document->createElement( 'group' );
			$this->groups_wrapper->appendChild( $this->groups[ $group_name ] );

			$this->add_group_attributes( $this->groups[ $group_name ], $ref );
		}

		if ( ! empty( $ref['field_id'] ) ) {
			// Make sure the encoding is the same for all sub-fields of a same meta.
			$field_group_name = sprintf(
				'%s|%s',
				$ref['field_type'],
				explode( '|', $ref['field_id'] )[0] // Meta sub-fields => meta name.
			);

			if ( array_key_exists( $field_group_name, $this->encodings ) ) {
				$ref['encoding'] = $this->encodings[ $field_group_name ];
			} else {
				$this->encodings[ $field_group_name ] = $ref['encoding'] ?? '';
			}
		} else {
			$ref['encoding'] = '';
		}

		if ( empty( $ref['encoding'] ) ) {
			// No encoding.
			return $group_name;
		}

		$parent_group = $this->groups[ $group_name ];
		$group_name  .= "|{$ref['encoding']}";

		if ( array_key_exists( $group_name, $this->groups ) ) {
			return $group_name;
		}

		// Create a new group.
		$this->groups[ $group_name ] = $this->document->createElement( 'group' );
		$parent_group->appendChild( $this->groups[ $group_name ] );

		$this->add_encoding_group_attributes( $this->groups[ $group_name ], $ref );

		return $group_name;
	}

	/**
	 * Adds the source and target tags to the DOM.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement $parent Parent tag the source and target will be added to.
	 * @param string     $source The source to be translated.
	 * @param string     $target A preexisting translation, if any.
	 * @return void
	 */
	protected function add_source_and_target( DOMElement $parent, string $source, string $target ) {
		$source_tag = $this->document->createElement( 'source' );
		$parent->appendChild( $source_tag );
		$source_tag->appendChild( $this->document->createCDATASection( $source ) );

		$target_tag = $this->document->createElement( 'target' );
		$parent->appendChild( $target_tag );

		if ( $target ) {
			$target_tag->appendChild( $this->document->createCDATASection( $target ) );
		}
	}

	/**
	 * Returns the current file extension.
	 *
	 * @since 3.6
	 *
	 * @return string The file extension.
	 */
	protected function get_extension(): string {
		return 'xliff';
	}

	/**
	 * Gets the xliff version.
	 *
	 * @since 3.6
	 *
	 * @return string The xliff version.
	 */
	abstract protected function get_version();

	/**
	 * Adds a source string to exported data and optionally a pre-existing translated one.
	 *
	 * @since 3.6
	 *
	 * @param array  $ref    {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @param string $source The source to be translated.
	 * @param string $target A preexisting translation, if any.
	 * @return void
	 *
	 * @phpstan-param translationEntryRef $ref
	 * @phpstan-param non-empty-string $source
	 */
	abstract protected function add_valid_translation_entry( array $ref, string $source, string $target );

	/**
	 * Adds attributes to a newly created group.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement $group Translation group we want to add attributes to.
	 * @param array      $ref   {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @return void
	 *
	 * @phpstan-param translationEntryRef $ref
	 */
	abstract protected function add_group_attributes( DOMElement $group, array $ref );

	/**
	 * Adds attributes to a newly created group meant for encoding purpose.
	 *
	 * @since 3.6
	 *
	 * @param DOMElement $group Translation group we want to add attributes to.
	 * @param array      $ref   {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type   Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type    Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id     A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id      Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $field_comment Optional, a comment meant for the translators.
	 *     @type string $encoding      Optional, encoding format for the field group.
	 * }
	 * @return void
	 *
	 * @phpstan-param translationEntryRef & array{encoding: non-falsy-string} $ref
	 */
	abstract protected function add_encoding_group_attributes( DOMElement $group, array $ref );
}
