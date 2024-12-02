<?php
/**
 * Class PLL_Translation_Metas
 *
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Abstract class to manage the import of metas.
 *
 * @since 3.3
 */
abstract class PLL_Translation_Metas {
	/**
	 * Meta type. Typically 'post' or 'term' and must be filled by the child class.
	 *
	 * @var string
	 */
	protected $meta_type;

	/**
	 * The context to translate entry.
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * Translations set where to look for the post metas translations.
	 *
	 * @var Translations
	 */
	protected $translations;

	/**
	 * Object to manage copied metas during import.
	 *
	 * @var PLL_Sync_Metas
	 */
	protected $sync_metas;

	/**
	 * Array containing meta keys to translate.
	 *
	 * @var array[] {
	 *     A list of arrays described as follow:
	 *
	 *     @type string   $meta_key       The name of the meta.
	 *     @type string[] $meta_sub_keys  The meta sub-fields to translate.
	 *     @type int      $value_position The position of the value in case of multiple values.
	 *     @type string   $encoding       Encoding format of the meta value.
	 * }
	 * @phpstan-var array<int, array{meta_key: string, meta_sub_keys: array<int, string>, value_position: int, encoding: string}>
	 */
	protected $metas_to_translate;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Sync_Metas $sync_metas Object to manage copied metas during import.
	 */
	public function __construct( PLL_Sync_Metas $sync_metas ) {
		$this->sync_metas = $sync_metas;
	}

	/**
	 * Translates the metas from a given object, whether it's a copy or a real translation.
	 *
	 * @since 3.3
	 *
	 * @param int          $src_object_id   Source object to get the metas from.
	 * @param int          $tr_object_id    Translated object to translate the metas from.
	 * @param PLL_Language $target_language Target language object.
	 * @param bool         $copy            Whether to copy source metas. For instance, if the translation is updated, there is no need to copy source metas.
	 * @return void
	 */
	public function translate( $src_object_id, $tr_object_id, PLL_Language $target_language, $copy ) {
		$this->metas_to_translate = $this->get_metas_to_translate();

		/**
		 * If source metas must be copied, let's filter them to remove further translated metas.
		 * This avoids to copy source meta value and add another translated value to it...
		 */
		if ( $copy ) {
			add_filter( "pll_copy_{$this->meta_type}_metas", array( $this, 'remove_metas_to_translate' ) );
			$this->sync_metas->copy( $src_object_id, $tr_object_id, $target_language->slug, false );
			remove_filter( "pll_copy_{$this->meta_type}_metas", array( $this, 'remove_metas_to_translate' ) );
		}

		$this->translate_metas_values( $src_object_id, $tr_object_id );
	}

	/**
	 * Setter for translations.
	 * Translations of the matching context are sanitized.
	 *
	 * @since 3.3
	 *
	 * @param Translations $translations A set of translations to search the metas translations in.
	 * @return void
	 */
	public function set_translations( Translations $translations ) {
		$this->translations = $translations;

		foreach ( $this->translations->entries as $key => $entry ) {
			if ( Context::get_field( $entry ) !== $this->context ) {
				continue;
			}

			foreach ( $entry->translations as $i => $translation ) {
				if ( $entry->singular === $translation || '' === $translation ) {
					continue;
				}

				$this->translations->entries[ $key ]->translations[ $i ] = wp_kses_post( $translation );
			}
		}
	}

	/**
	 * Removes meta keys to translate from an array of meta to copy.
	 *
	 * @since 3.3
	 *
	 * @param string[] $meta_keys Meta keys to copy.
	 * @return string[] Filtered array of meta to copy.
	 */
	public function remove_metas_to_translate( $meta_keys ) {
		return array_diff( $meta_keys, wp_list_pluck( $this->metas_to_translate, 'meta_key' ) );
	}

	/**
	 * Translates metas values.
	 *
	 * @since 3.3
	 *
	 * @param int $src_object_id Source object id.
	 * @param int $tr_object_id  Translated object id.
	 * @return void
	 */
	private function translate_metas_values( $src_object_id, $tr_object_id ) {
		$src_metas = get_metadata( $this->meta_type, $src_object_id );

		if ( ! is_array( $src_metas ) || empty( $src_metas ) ) {
				return;
		}

		$tr_metas = array();
		$formats  = array();

		foreach ( $this->metas_to_translate as $meta ) {
			$meta_key = $meta['meta_key'];

			if ( empty( $src_metas[ $meta_key ] ) ) {
				// Exported meta key doesn't exist anymore ?!
				continue;
			}

			if ( ! isset( $formats[ $meta_key ] ) ) {
				$formats[ $meta_key ] = $meta['encoding'];
			}

			$src_meta_values = $src_metas[ $meta_key ];
			$decoder         = new PLL_Data_Encoding( $formats[ $meta_key ] );

			if ( ! empty( $meta['meta_sub_keys'] ) && is_array( $src_meta_values ) && count( $src_meta_values ) > 1 && ! $this->has_only_scalar_values( $src_meta_values, $decoder ) ) {
				// Do not import meta with multiple non scalar values.
				continue;
			}

			if ( ! isset( $src_meta_values[ $meta['value_position'] ] ) ) {
				// Meta value doesn't match.
				continue;
			}

			if ( isset( $tr_metas[ $meta_key ][ $meta['value_position'] ] ) ) {
				// Meta has already been decoded and translated, but other subfields remain to be processed.
				$value_to_translate = $tr_metas[ $meta_key ][ $meta['value_position'] ];
			} else {
				$value_to_translate = $src_meta_values[ $meta['value_position'] ];

				if ( ! empty( $meta['meta_sub_keys'] ) ) {
					// Sub field has to be translated, let's decode its value.
					if ( $decoder->decode_reference( $value_to_translate )->has_errors() ) {
						// Error while decoding.
						continue;
					}
				}
			}

			$tr_metas[ $meta_key ][ $meta['value_position'] ] = $this->maybe_translate_metas_sub_fields( $value_to_translate, $meta );
		}

		// Re-encode.
		$formats = array_intersect_key( $formats, $tr_metas );

		foreach ( $formats as $meta_key => $format ) {
			$decoder = new PLL_Data_Encoding( $format );

			if ( $decoder->use_serialize() ) {
				// `update_metadata()` will serialize it.
				continue;
			}

			foreach ( $tr_metas[ $meta_key ] as &$tr_meta ) {
				$decoder->encode_reference( $tr_meta );
			}
		}

		$this->insert_translated_metas( $tr_object_id, $tr_metas );
	}

	/**
	 * Inserts the translated metas into the database and
	 * takes care to add multiples meta values if needed.
	 * Note that if a meta has several values that aren't scalar,
	 * they won't be inserted in the database to avoid to
	 * delete potential useful data.
	 *
	 * @since 3.3
	 *
	 * @param int   $tr_object_id Translated object id.
	 * @param array $tr_metas     Translated metas value(s).
	 * @return void
	 */
	private function insert_translated_metas( $tr_object_id, array $tr_metas ) {
		$this->sync_metas->remove_all_meta_actions();
		foreach ( $tr_metas as $meta_key => $values ) {
			$slashed_key = wp_slash( $meta_key );

			// $values is an indexed array, so it contains one or more values?
			if ( 1 < count( $values ) ) {
				// To update multiple meta values, it's easier to delete and add rather than attempting to update them individually.
				delete_metadata( $this->meta_type, $tr_object_id, $slashed_key );
				foreach ( $values as $value ) {
					add_metadata( $this->meta_type, $tr_object_id, $slashed_key, wp_slash( $value ) ); // Multiple meta values must be added one by one.
				}
			} else {
				// $values contains a single meta value, let's take it.
				update_metadata( $this->meta_type, $tr_object_id, $slashed_key, wp_slash( reset( $values ) ) );
			}
		}
		$this->sync_metas->add_all_meta_actions();
	}

	/**
	 * Returns the metas to translate from the translations entries.
	 * Each meta translation entry is identified by a concatenation of
	 * meta key, subfields and position (or index) of the meta.
	 * For instance: 'meta_key|with|sub|fields:2'.
	 *
	 * @since 3.3
	 *
	 * @return array[] {
	 *     A list of arrays described as follows:
	 *
	 *     @type string   $meta_key       The name of the meta.
	 *     @type string[] $meta_sub_keys  The meta subfields to translate.
	 *     @type int      $value_position The position of the value in case of multiple values.
	 *     @type string   $encoding       Encoding format of the meta value.
	 *     @type string   $Context::ID     Id key from entry context, useful to translate back.
	 * }
	 *
	 * @phpstan-return array<int, array{meta_key: string, meta_sub_keys: array<int, string>, value_position: int, encoding: string}>
	 */
	private function get_metas_to_translate() {
		$metas = array();

		foreach ( $this->translations->entries as $entry ) {
			if ( Context::get_field( $entry ) !== $this->context ) {
				continue;
			}

			$meta_identifier = Context::get_id( $entry );
			if ( empty( $meta_identifier ) ) {
				continue;
			}

			preg_match( '/^(?<subkeys>.+)(?::(?<position>\d+))?$/U', $meta_identifier, $matches ); // Extract position (i.e. index) of the meta string.
			$position = isset( $matches['position'] ) ? absint( $matches['position'] ) : 0;
			$sub_keys = preg_split( '/(?<!\\\)[|]/', $matches['subkeys'] ); // Extract all subkeys from meta string.
			if ( ! $sub_keys ) {
				$sub_keys = array();
			}
			$sub_keys = array_map( 'stripcslashes', $sub_keys ); // Remove backslashes from escaped pipes.
			$meta_key = array_shift( $sub_keys );

			$metas[] = array(
				'meta_key'       => ! empty( $meta_key ) ? $meta_key : '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_sub_keys'  => $sub_keys,
				'value_position' => $position,
				'encoding'       => Context::get_encoding( $entry ),
				'context_id'     => $meta_identifier,
			);
		}

		return $metas;
	}

	/**
	 * Translates meta subfields recursively.
	 *
	 * @since 3.3
	 *
	 * @param mixed $meta_value Meta value(s) to translate.
	 * @param array $meta {
	 *     An array with the meta_key, subfields to translate (ordered by dimension) and value position.
	 *
	 *     @type array  $meta_sub_keys  The meta sub keys.
	 *     @type int    $value_position The value position.
	 *     @type string $encoding       Meta encoding.
	 *     @type string $Context::ID     Id key from entry context, useful to translate back.
	 * }
	 * @return mixed Translated meta value(s).
	 */
	private function maybe_translate_metas_sub_fields( $meta_value, array $meta ) {
		$value_position  = $meta['value_position'];
		$sub_keys        = $meta['meta_sub_keys'];

		if ( ! is_array( $meta_value ) && ! is_scalar( $meta_value ) ) { // We're not able to translate something else for now.
			return $meta_value;
		}

		if ( empty( $sub_keys ) ) { // No sub key to translate, let's process the current value.
			if ( is_scalar( $meta_value ) ) {
				$meta_value = $this->translations->translate(
					(string) $meta_value,
					Context::to_string(
						array(
							Context::FIELD    => $this->context,
							Context::ID       => $meta['context_id'],
							Context::ENCODING => $meta['encoding'],
						)
					)
				);
			}

			return $meta_value;
		}

		$first_key = array_shift( $sub_keys ); // Let's get the first subfield key to process.
		if ( ! is_array( $meta_value ) || ! isset( $meta_value[ $first_key ] ) ) { // Meta sub key doesn't match?!
			return $meta_value;
		}

		if ( empty( $sub_keys ) ) { // No more sub keys to translate.
			if ( is_scalar( $meta_value[ $first_key ] ) ) {
				$meta_value[ $first_key ] = $this->translations->translate(
					(string) $meta_value[ $first_key ],
					Context::to_string(
						array(
							Context::FIELD    => $this->context,
							Context::ID       => $meta['context_id'],
							Context::ENCODING => $meta['encoding'],
						)
					)
				);
			}

			return $meta_value;
		}

		$meta_value[ $first_key ] = $this->maybe_translate_metas_sub_fields(
			$meta_value[ $first_key ],
			array(
				'meta_sub_keys'  => $sub_keys,
				'value_position' => $value_position,
				'encoding'       => $meta['encoding'],
				'context_id'     => $meta['context_id'],
			)
		);

		return $meta_value;
	}

	/**
	 * Asserts an array contains only scalar values.
	 *
	 * @since 3.3
	 * @since 3.6 Added parameter `$decoder`.
	 *
	 * @param array             $array   Array to check.
	 * @param PLL_Data_Encoding $decoder Data decoder.
	 * @return bool True if the array contains only scalar values, false otherwise.
	 */
	private function has_only_scalar_values( array $array, PLL_Data_Encoding $decoder ) {
		foreach ( $array as $value ) {
			$value = $decoder->decode( $value );
			if ( ! is_scalar( $value ) ) {
				return false;
			}
		}

		return true;
	}
}
