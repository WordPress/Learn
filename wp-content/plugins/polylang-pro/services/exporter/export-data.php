<?php
/**
 * @package Polylang-Pro
 */

/**
 * Abstract class representing exported data for a specific source/target language pair.
 *
 * @phpstan-type translationEntryRef array{object_type: non-falsy-string, field_type: non-falsy-string, object_id: int, field_id?: string, field_comment?: string, encoding?: string}
 *
 * @since 3.6
 */
abstract class PLL_Export_Data {
	/**
	 * The registered source language.
	 *
	 * @var PLL_Language
	 */
	protected $source_language;

	/**
	 * The registered target language.
	 *
	 * @var PLL_Language
	 */
	protected $target_language;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $source_language The export's source language.
	 * @param PLL_Language $target_language The export's target language.
	 */
	public function __construct( PLL_Language $source_language, PLL_Language $target_language ) {
		$this->source_language = $source_language;
		$this->target_language = $target_language;
	}

	/**
	 * Returns the source language.
	 *
	 * @since 3.1
	 * @since 3.6 Returns a `PLL_Language` object.
	 *            Is public and concrete.
	 *
	 * @return PLL_Language Source language object.
	 */
	public function get_source_language(): PLL_Language {
		return $this->source_language;
	}

	/**
	 * Returns the target language.
	 *
	 * @since 3.1
	 * @since 3.6 Returns a `PLL_Language` object.
	 *            Is public and concrete.
	 *
	 * @return PLL_Language Target language object.
	 */
	public function get_target_language(): PLL_Language {
		return $this->target_language;
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
	abstract public function add_translation_entry( array $ref, string $source, string $target = '' );

	/**
	 * Checks the parameters given are valid.
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
	 * @return bool True if valid, false otherwise.
	 *
	 * @phpstan-param translationEntryRef $ref
	 * @phpstan-param non-empty-string $source
	 */
	public function are_entry_parameters_valid( array $ref, string $source ): bool {
		return '' !== $source
			&& isset( $ref['object_id'] ) && is_numeric( $ref['object_id'] ) && (int) $ref['object_id'] >= 0
			&& ! empty( $ref['object_type'] ) && is_string( $ref['object_type'] )
			&& ! empty( $ref['field_type'] ) && is_string( $ref['field_type'] );
	}

	/**
	 * Returns exported data.
	 *
	 * @since 3.6
	 *
	 * @return mixed
	 */
	abstract public function get();
}
