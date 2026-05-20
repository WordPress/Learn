<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Machine_Translation;

use PLL_Export_Data;
use Translations;
use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Class to store data to translation with machine translation,
 * organized by object type and object IDs respectively.
 *
 * @phpstan-import-type translationEntryRef from PLL_Export_Data
 */
class Data extends PLL_Export_Data {
	/**
	 * Array of translated data, ordered by type and IDs.
	 *
	 * @var Translations[][]
	 *
	 * @phpstan-var array<non-falsy-string, array<int, Translations>>
	 */
	private $translations = array();

	/**
	 * Adds a source string to exported data and optionally a pre-existing translated one.
	 * New types or objects are prepended to data arrays, assuming they are linked to previously added ones.
	 * Once translated, this allows to import linked objects before the objects they are linked to.
	 * For example, a category is imported before the post it is linked to.
	 *
	 * @since 3.6
	 *
	 * @param array  $ref    {
	 *     Array containing the content type and optionally the corresponding object ID.
	 *
	 *     @type string $object_type Object type to be exported (e.g. `post` or `term`).
	 *     @type string $field_type  Field type to be exported (e.g. `post_content`, `post_title`...).
	 *     @type int    $object_id   A unique identifier to retrieve the corresponding object from the database.
	 *     @type string $field_id    Optional, a unique identifier to retrieve the corresponding field from the database.
	 *     @type string $encoding    Optional, encoding format for the field group.
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

		if ( ! isset( $this->translations[ $ref['object_type'] ] ) ) {
			$this->translations = array( $ref['object_type'] => array() ) + $this->translations;
		}

		if ( ! isset( $this->translations[ $ref['object_type'] ][ $ref['object_id'] ] ) ) {
			$this->translations[ $ref['object_type'] ] = array( $ref['object_id'] => new Translations() ) + $this->translations[ $ref['object_type'] ];
		}

		$context = Context::to_string(
			array(
				Context::FIELD    => $ref['field_type'],
				Context::ID       => isset( $ref['field_id'] ) ? $ref['field_id'] : '',
				Context::ENCODING => isset( $ref['encoding'] ) ? $ref['encoding'] : '',
			)
		);
		$this->translations[ $ref['object_type'] ][ $ref['object_id'] ]->add_entry(
			array(
				'singular'     => $source,
				'translations' => array( $target ),
				'context'      => $context,
			)
		);
	}

	/**
	 * Returns translated data.
	 *
	 * @since 3.6
	 *
	 * @return array
	 *
	 * @phpstan-return array<non-falsy-string, array<int, Translations>>
	 */
	public function get(): array {
		return $this->translations;
	}
}
