<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.1
 */

/**
 * Xliff 1.2 file, generated from exporting Polylang translations.
 *
 * @phpstan-import-type translationEntryRef from PLL_Export_Data
 *
 * @since 3.1
 */
class PLL_Xliff_Export_12 extends PLL_Xliff_Export_Base {
	/**
	 * Name of the XML property used to store additional data.
	 *
	 * @var string
	 */
	const EXTRA_DATA_PROP_NAME = 'extradata';

	/**
	 * Constructor.
	 *
	 * @since 3.1
	 * @since 3.6 Added `$source_language` and `$target_language` parameters.
	 *
	 * @param PLL_Language $source_language The source language of the exported data.
	 * @param PLL_Language $target_language The target language of the exported data.
	 */
	public function __construct( PLL_Language $source_language, PLL_Language $target_language ) {
		parent::__construct( $source_language, $target_language );

		$xliff = $this->add_child_element(
			$this->document,
			'xliff',
			array(
				'version' => $this->get_version(),
				'xmlns'   => 'urn:oasis:names:tc:xliff:document:1.2',
			)
		);

		$file = $this->add_child_element(
			$xliff,
			'file',
			array(
				'datatype'        => 'plaintext',
				'original'        => get_site_url(),
				'product-name'    => PLL_Import_Export::APP_NAME,
				'product-version' => POLYLANG_VERSION,
				'source-language' => $source_language->get_locale( 'display' ),
				'target-language' => $target_language->get_locale( 'display' ),
			)
		);

		$this->groups_wrapper = $this->add_child_element( $file, 'body' );
	}

	/**
	 * Gets the xliff version.
	 *
	 * @since 3.6
	 *
	 * @return string The xliff version.
	 */
	protected function get_version() {
		return '1.2';
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
	 * @param string $target A preexisting translation, if any.
	 * @return void
	 *
	 * @phpstan-param translationEntryRef $ref
	 * @phpstan-param non-empty-string $source
	 */
	protected function add_valid_translation_entry( array $ref, string $source, string $target ) {
		$group_name = $this->get_source_reference( $ref );
		$unit_tag   = $this->document->createElement( 'trans-unit' );
		$this->groups[ $group_name ]->appendChild( $unit_tag );

		$unit_tag->setAttribute( 'id', (string) ++$this->units_count );
		$unit_tag->setAttribute( 'restype', 'x-' . $ref['field_type'] );

		if ( ! empty( $ref['field_id'] ) ) {
			$unit_tag->setAttribute( 'resname', $ref['field_id'] );
		}

		if ( ! empty( $ref['field_comment'] ) ) {
			$note_tag = $this->document->createElement( 'note' );
			$note_tag->appendChild( $this->document->createCDATASection( $ref['field_comment'] ) );
			$unit_tag->appendChild( $note_tag );
		}

		$this->add_source_and_target( $unit_tag, $source, $target );
	}

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
	protected function add_group_attributes( DOMElement $group, array $ref ) {
		$group->setAttribute( 'restype', 'x-' . $ref['object_type'] );

		if ( ! empty( $ref['object_id'] ) ) {
			$group->setAttribute( 'resname', (string) $ref['object_id'] );
		}
	}

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
	protected function add_encoding_group_attributes( DOMElement $group, array $ref ) {
		$group->setAttribute( self::EXTRA_DATA_PROP_NAME, 'encoding:' . $ref['encoding'] );
	}
}
