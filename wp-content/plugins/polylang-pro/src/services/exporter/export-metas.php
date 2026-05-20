<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Services\Encoding\Data_Encoding;

/**
 * Abstract class to manage the export of metas.
 *
 * @since 3.3
 */
abstract class PLL_Export_Metas {
	/**
	 * Meta type. Typically 'post' or 'term' and must be filled by the child class.
	 *
	 * @var string
	 *
	 * @phpstan-var non-falsy-string
	 */
	protected $meta_type;

	/**
	 * Import/Export meta type. {@see PLL_Import_Export::POST_META} or {@see PLL_Import_Export::POST_META} and must be filled by the child class.
	 *
	 * @var string
	 *
	 * @phpstan-var non-falsy-string
	 */
	protected $import_export_meta_type;

	/**
	 * Returns the meta names to export.
	 *
	 * @since 3.3
	 *
	 * @param int $from ID of the source object.
	 * @param int $to   ID of the target object.
	 * @return array List of custom fields names.
	 */
	protected function get_meta_names_to_export( int $from, int $to ): array {
		/**
		 * Filters the meta names to export.
		 *
		 * @since 3.3
		 *
		 * @param array $keys A multi-dimensional array containing nested meta sub keys to translate. Wildcards (`*`) can be
		 *                    used to match any characters. If `*` are already present in the meta name or sub-key,
		 *                    escape them with a backslash: `\*`.
		 *     @example array(
		 *         'meta_to_translate_1' => 1,
		 *         'meta_to_translate_2' => 1,
		 *         'meta_to_translate_3' => array(
		 *             'sub_key_to_translate_1' => 1,
		 *             'sub_key_to_translate_2' => array(
		 *                 'sub_sub_key_to_translate_1' => 1,
		 *             ),
		 *             'sub_key_is_an_array_with_all_values_to_translate' => 1,
		 *         ),
		 *         'meta_name_*'         => array(
		 *             '*' => array(
		 *                 'sub_key_*_to_translate' => 1,
		 *             ),
		 *         ),
		 *     )
		 * @param int $from ID of the source object.
		 * @param int $to   ID of the target object.
		 */
		return (array) apply_filters(
			"pll_{$this->meta_type}_metas_to_export",
			$this->get_default_metas_to_export(),
			$from,
			$to
		);
	}

	/**
	 * Returns the default metas to export.
	 *
	 * @since 3.8
	 *
	 * @return array A multi-dimensional array containing nested meta sub keys to translate. Default empty array.
	 */
	protected function get_default_metas_to_export(): array {
		return array();
	}

	/**
	 * Returns the encodings to use for metas.
	 *
	 * @since 3.6
	 *
	 * @param int $from ID of the source object.
	 * @param int $to   ID of the target object.
	 * @return array List of custom fields encodings.
	 */
	protected function get_meta_encodings( int $from, int $to ): array {
		/**
		 * Filters the encodings to use for metas.
		 * Metas that are serialized do not need to be listed here since WordPress automatically decodes this format.
		 *
		 * @since 3.6
		 *
		 * @param array $keys A multi-dimensional array containing nested meta sub keys to translate. Wildcards (`*`) can be
		 *                    used to match any characters. If `*` are already present in the meta name or sub-key,
		 *                    escape them with a backslash: `\*`.
		 *     @example array(
		 *        'meta_to_translate_1' => 'json',
		 *        'meta_name_*_foobar'  => 'json',
		 *    )
		 * @param int $from ID of the source object.
		 * @param int $to   ID of the target object.
		 */
		return (array) apply_filters(
			"pll_{$this->meta_type}_meta_encodings",
			$this->get_default_meta_encodings(),
			$from,
			$to
		);
	}

	/**
	 * Returns the default encodings to use for metas.
	 *
	 * @since 3.8
	 *
	 * @return array A multi-dimensional array containing nested meta sub keys to translate. Default empty array.
	 */
	protected function get_default_meta_encodings(): array {
		return array();
	}

	/**
	 * Export metas to translate, along their translated values if possible.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Export_Data $export Export object.
	 * @param int             $from   ID of the source object.
	 * @param int             $to     ID of the target object.
	 * @return void
	 */
	public function export( PLL_Export_Data $export, int $from, int $to = 0 ) {
		$meta_names_to_export = $this->get_meta_names_to_export( $from, $to );

		if ( empty( $meta_names_to_export ) ) {
			return;
		}

		$source_metas = get_metadata( $this->meta_type, $from );

		if ( empty( $source_metas ) || ! is_array( $source_metas ) ) {
			return;
		}

		$tr_metas  = get_metadata( $this->meta_type, $to );
		$tr_metas  = is_array( $tr_metas ) ? $tr_metas : array();
		$encodings = $this->get_meta_encodings( $from, $to );
		$matcher   = new PLL_Format_Util();

		foreach ( $meta_names_to_export as $meta_name => $meta_subfield ) {
			$entries = $matcher->filter_list( $source_metas, (string) $meta_name );

			foreach ( $entries as $meta_name => $meta_values ) {
				$tr_meta_values          = $tr_metas[ $meta_name ] ?? array();
				$encodings[ $meta_name ] = $encodings[ $meta_name ] ?? '';
				$decoder                 = new Data_Encoding( $encodings[ $meta_name ] );
				$index                   = 0;

				foreach ( $meta_values as $value_index => $meta_value ) {
					if ( $decoder->decode_reference( $meta_value )->has_errors() ) {
						// Error while decoding.
						continue;
					}

					$meta_subfield = is_array( $meta_subfield ) ? $meta_subfield : array();
					$tr_value      = array();

					if ( isset( $tr_meta_values[ $value_index ] ) ) {
						$tr_value = $tr_meta_values[ $value_index ];
						if ( $decoder->decode_reference( $tr_value )->has_errors() ) {
							// Error while decoding.
							$tr_value = array();
						}
					}

					$index += (int) $this->maybe_export_metas_sub_fields(
						$meta_subfield,
						$this->escape_key( $meta_name ),
						$index,
						$meta_value,
						$tr_value,
						$from,
						$export,
						$encodings[ $meta_name ]
					);
				}
			}
		}
	}

	/**
	 * Maybe exports metas sub fields recursively if the given meta values is contained in the fields to export.
	 *
	 * @since 3.3
	 * @since 3.6 New parameter `$object_id`.
	 *
	 * @param array           $fields_to_export  A recursive array containing nested meta sub keys to translate.
	 *     @example array(
	 *        'sub_key_to_translate_1' => 1,
	 *        'sub_key_to_translate_2' => array(
	 *             'sub_sub_key_to_translate_1' => 1,
	 *         ),
	 *      ),
	 *    )
	 * @param string          $parent_key_string A string containing parent keys separated with pipes. Each pipe in key
	 *                                           should be escaped to avoid conflicts.
	 * @param int             $index             Index of the current meta value. Useful when a meta has several values.
	 * @param mixed           $source_metas      The source post metas.
	 * @param mixed           $tr_metas          The translated post metas.
	 * @param int             $object_id         ID of the object the meta belongs to.
	 * @param PLL_Export_Data $export            Export object.
	 * @param string          $encoding          Encoding format for the field group.
	 * @return bool True if the meta value has been exported, false otherwise.
	 */
	protected function maybe_export_metas_sub_fields( array $fields_to_export, string $parent_key_string, int $index, $source_metas, $tr_metas, int $object_id, PLL_Export_Data $export, string $encoding = '' ): bool {
		$source_metas = $this->type_value( $source_metas );
		if ( empty( $source_metas ) ) {
			return false;
		}

		$tr_metas = $this->type_value( $tr_metas );

		if ( is_string( $source_metas ) ) {
			if ( ! empty( $fields_to_export ) ) {
				// Invalid case.
				return false;
			}

			$id_suffix = 0 < $index ? ":{$index}" : '';
			$export->add_translation_entry(
				array(
					'object_type' => $this->meta_type,
					'field_type'  => $this->import_export_meta_type,
					'object_id'   => $object_id,
					'field_id'    => $parent_key_string . $id_suffix,
					'encoding'    => $encoding,
				),
				$source_metas,
				is_string( $tr_metas ) ? $tr_metas : ''
			);
			return true;
		}

		// Export some or all sub-values.
		$is_exported = false;

		if ( ! empty( $fields_to_export ) ) {
			// We still have some sub-values to export.
			$matcher = new PLL_Format_Util();

			foreach ( $fields_to_export as $key => $field_value ) {
				$entries = $matcher->filter_list( $source_metas, (string) $key );

				foreach ( $entries as $key => $meta_values ) {
					$is_exported = $this->maybe_export_metas_sub_fields(
						is_array( $field_value ) ? $field_value : array(),
						$this->build_key( $parent_key_string, $key ),
						$index,
						$meta_values,
						$this->get_sub_value( $tr_metas, $key ),
						$object_id,
						$export,
						$encoding
					) || $is_exported;
				}
			}

			return $is_exported;
		}

		// Export all sub-values.
		foreach ( $source_metas as $sub_field_index => $source_value ) {
			$is_exported = $this->maybe_export_metas_sub_fields(
				array(),
				$this->build_key( $parent_key_string, $sub_field_index ),
				$index,
				$source_value,
				$this->get_sub_value( $tr_metas, $sub_field_index ),
				$object_id,
				$export,
				$encoding
			) || $is_exported;
		}

		return $is_exported;
	}

	/**
	 * Returns a sub-value from an array or object.
	 * Objects are casted to array.
	 *
	 * @since 3.8
	 *
	 * @param mixed      $values The array or object containing the sub-value.
	 * @param string|int $key    The array key or object property name.
	 * @return mixed The sub-value. An empty array if the sub-value is not found.
	 */
	private function get_sub_value( $values, $key ) {
		$values = $this->to_array( $values );
		return $values[ $key ] ?? array();
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data The data to convert.
	 * @return array An array of data.
	 */
	private function to_array( $data ): array {
		if ( is_array( $data ) ) {
			return $data;
		}
		if ( is_object( $data ) ) {
			return get_object_vars( $data );
		}
		return array();
	}

	/**
	 * Types the given meta value.
	 * Returns an empty array if the type is not supported.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data The value to type.
	 * @return array|string
	 */
	private function type_value( $data ) {
		if ( is_array( $data ) ) {
			return $data;
		}
		if ( is_object( $data ) ) {
			return get_object_vars( $data );
		}
		if ( is_scalar( $data ) ) {
			return (string) $data;
		}
		return array();
	}

	/**
	 * Escapes `|` characters.
	 *
	 * @since 3.8
	 *
	 * @param string $key A key.
	 * @return string
	 */
	private function escape_key( string $key ): string {
		return addcslashes( $key, '\\|' );
	}

	/**
	 * Builds a key string.
	 *
	 * @since 3.8
	 *
	 * @param string $parent_key Parent key.
	 * @param string $key        Sub-key.
	 * @return string
	 */
	private function build_key( string $parent_key, string $key ): string {
		$escaped_key = $this->escape_key( $key );
		return "{$parent_key}|{$escaped_key}";
	}
}
