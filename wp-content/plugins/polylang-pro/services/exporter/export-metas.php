<?php
/**
 * @package Polylang-Pro
 */

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
		 * @param array $keys A recursive array containing nested meta sub keys to translate. Wildcards (`*`) can be
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
		return (array) apply_filters( "pll_{$this->meta_type}_metas_to_export", array(), $from, $to );
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
		 * @param array $keys A recursive array containing nested meta sub keys to translate. Wildcards (`*`) can be
		 *                    used to match any characters. If `*` are already present in the meta name or sub-key,
		 *                    escape them with a baclslash: `\*`.
		 *     @example array(
		 *        'meta_to_translate_1' => 'json',
		 *        'meta_name_*_foobar'  => 'json',
		 *    )
		 * @param int $from ID of the source object.
		 * @param int $to   ID of the target object.
		 */
		return (array) apply_filters( "pll_{$this->meta_type}_meta_encodings", array(), $from, $to );
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
				$decoder                 = new PLL_Data_Encoding( $encodings[ $meta_name ] );
				$index                   = 0;

				foreach ( $meta_values as $value_index => $meta_value ) {
					if ( $decoder->decode_reference( $meta_value )->has_errors() ) {
						// Error while decoding.
						continue;
					}

					$meta_subfield = is_array( $meta_subfield ) ? $meta_subfield : array();
					$tr_value      = isset( $tr_meta_values[ $value_index ] ) ? $tr_meta_values[ $value_index ] : array();

					if ( $decoder->decode_reference( $tr_value )->has_errors() ) {
						// Error while decoding.
						$tr_value = array();
					}

					$index += (int) $this->maybe_export_metas_sub_fields(
						$meta_subfield,
						addcslashes( $meta_name, '\\|' ),
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
	 * @param int             $index             Index of the current meta value. Usefull when a meta has several values.
	 * @param array|string    $source_metas      The source post metas.
	 * @param array|string    $tr_metas          The translated post metas.
	 * @param int             $object_id         ID of the object the meta belongs to.
	 * @param PLL_Export_Data $export            Export object.
	 * @param string          $encoding          Encoding format for the field group.
	 * @return bool True if the meta value has been exported, false otherwise.
	 */
	protected function maybe_export_metas_sub_fields( array $fields_to_export, string $parent_key_string, int $index, $source_metas, $tr_metas, int $object_id, PLL_Export_Data $export, string $encoding = '' ): bool {
		$is_exported = false;

		if ( ! empty( $fields_to_export ) ) {
			if ( ! is_array( $source_metas ) ) {
				return false;
			}

			$matcher = new PLL_Format_Util();

			foreach ( $fields_to_export as $key => $field_value ) {
				$entries = $matcher->filter_list( $source_metas, (string) $key );

				foreach ( $entries as $key => $meta_values ) {
					$escaped_key = addcslashes( (string) $key, '\\|' );
					$key_string  = "$parent_key_string|$escaped_key";
					$sub_field   = is_array( $field_value ) ? $field_value : array();

					$is_exported = $this->maybe_export_metas_sub_fields(
						$sub_field,
						$key_string,
						$index,
						$meta_values,
						isset( $tr_metas[ $key ] ) ? $tr_metas[ $key ] : array(),
						$object_id,
						$export,
						$encoding
					) || $is_exported;
				}
			}

			return $is_exported;
		}

		$id_suffix = 0 < $index ? ":{$index}" : '';

		if ( is_scalar( $source_metas ) ) {
			// Single value to export doesn't require any index.
			$source_metas = (string) $source_metas;

			if ( '' === $source_metas ) {
				return false;
			}

			$export->add_translation_entry(
				array(
					'object_type' => $this->meta_type,
					'field_type'  => $this->import_export_meta_type,
					'object_id'   => $object_id,
					'field_id'    => $parent_key_string . $id_suffix,
					'encoding'    => $encoding,
				),
				$source_metas,
				is_scalar( $tr_metas ) ? (string) $tr_metas : ''
			);
			return true;
		}

		if ( ! is_array( $source_metas ) ) {
			return false;
		}

		$tr_metas = (array) $tr_metas;
		foreach ( $source_metas as $sub_field_index => $source_value ) {
			if ( '' === $source_value ) {
				continue;
			}

			$escaped_key = addcslashes( (string) $sub_field_index, '\\|' );
			$tr_values   = isset( $tr_metas[ $sub_field_index ] ) ? $tr_metas[ $sub_field_index ] : '';
			$export->add_translation_entry(
				array(
					'object_type' => $this->meta_type,
					'field_type'  => $this->import_export_meta_type,
					'object_id'   => $object_id,
					'field_id'    => "{$parent_key_string}|{$escaped_key}{$id_suffix}",
					'encoding'    => $encoding,
				),
				$source_value,
				$tr_values
			);
			$is_exported = true;
		}

		return $is_exported;
	}
}
