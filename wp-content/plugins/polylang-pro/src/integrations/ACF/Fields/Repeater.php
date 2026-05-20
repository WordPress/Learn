<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Fields;

/**
 * This class is part of the ACF compatibility.
 * Class that allows to copy the values of repeater fields (mostly useful if the repeater is with paginated).
 *
 * @since 3.8
 */
class Repeater {
	/**
	 * Changes some HTML attributes of a repeater sub-field to make it look like a newly added field. This is required
	 * when the repeater's pagination is enabled, otherwise the sub-fields won't be saved.
	 * The `name` attribute of a saved sub-field value looks like `acf[field_682455496dc8e][row-0][field_6824563045bb9]`.
	 * The `name` attribute of a new sub-field value looks like `acf[field_682455496dc8e][682704342a536][field_6824563045bb9]`.
	 * If a sub-field doesn't contain `row`, ACF will discard it because it isn't actually in the database.
	 *
	 * @since 3.8
	 * @see acf_field_repeater::update_value()
	 *      ACF_Repeater_Table::row()
	 *
	 * @param array $field Custom field definition. Only sub-fields of paginated repeaters are handled.
	 * @return array
	 */
	public static function change_row_keys( $field ) {
		if ( empty( $field['parent_repeater'] ) || false === strpos( $field['name'], '[row-' ) ) {
			return $field;
		}

		$parent_field = acf_get_field( $field['parent_repeater'] );

		if ( empty( $parent_field ) || ! self::is_paginated( $parent_field ) ) {
			return $field;
		}

		/*
		 * Instead of a random hash like ACF does for new rows, provide a key that contains the original row number, but
		 * not `row`. This key will be reverted in `reset_row_keys()`.
		 */
		$field['name']   = preg_replace( '/\[row-(\d+)\]/', '[pll-$1]', $field['name'] );
		$field['prefix'] = preg_replace( '/\[row-(\d+)\]/', '[pll-$1]', $field['prefix'] );
		$field['id']     = preg_replace( '/-row-(\d+)-/', '-pll-$1-', $field['id'] );

		return $field;
	}

	/**
	 * Resets the array keys that look like `pll-0`: they correspond to sub-fields of a repeater with pagination.
	 * They are used when creating a post translation. Those custom keys are changed back to their original value
	 * to prevent creating new rows in the original post (reverse sync).
	 * See `self::change_row_keys()`.
	 *
	 * @since 3.8
	 *
	 * @param mixed $value A field value.
	 * @param array $field Custom field definition. Only sub-fields of paginated repeaters are handled.
	 * @return mixed
	 */
	public static function reset_row_keys( $value, array $field ) {
		if ( ! is_array( $value ) || empty( $value ) || ! self::is_paginated( $field ) ) {
			return $value;
		}

		$new_value = array();

		foreach ( $value as $k => $v ) {
			if ( is_string( $k ) && preg_match( '/^pll-(?<i>\d+)$/', $k, $matches ) ) {
				$k = "row-{$matches['i']}";
			}

			if ( is_array( $v ) ) {
				if ( isset( $new_value[ $k ] ) && is_array( $new_value[ $k ] ) ) {
					// In case `$value` has `row-0` and `pll-0` at the same time (it happened, don't ask how).
					$v = array_merge( $new_value[ $k ], $v );
				}
			}

			$new_value[ $k ] = $v;
		}

		return $new_value;
	}

	/**
	 * Tells if the given field is a paginated repeater.
	 *
	 * @since 3.8
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	public static function is_paginated( array $field ): bool {
		return 'repeater' === $field['type'] && ! empty( $field['pagination'] ) && ! empty( $field['sub_fields'] );
	}

	/**
	 * Returns the field value.
	 *
	 * @since 3.8
	 *
	 * @param int|string $acf_id ACF post ID.
	 * @param array      $field  Custom field definition.
	 * @return mixed
	 */
	public static function get_value( $acf_id, array $field ) {
		if ( ! self::is_paginated( $field ) ) {
			return acf_get_value( $acf_id, $field );
		}

		$repeater = acf_get_field_type( 'repeater' );

		if ( ! $repeater ) {
			// The active plugin is ACF while a repeater was previously created with ACF Pro
			return acf_get_value( $acf_id, $field );
		}

		// Disable pagination to get all values.
		$is_rendering = $repeater->is_rendering;
		$repeater->is_rendering = false;

		$value = acf_get_value( $acf_id, $field );

		$repeater->is_rendering = $is_rendering;
		return $value;
	}
}
