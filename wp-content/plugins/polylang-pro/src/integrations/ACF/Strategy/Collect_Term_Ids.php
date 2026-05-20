<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

/**
 * This class is part of the ACF compatibility.
 * The collect term IDs strategy.
 * Gathers the IDs of the linked terms prior to export.
 *
 * @since 3.7
 */
class Collect_Term_Ids extends Abstract_Collect_Ids {

	/**
	 * Collects the term IDs of a field.
	 *
	 * @since 3.7
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @return int[] Custom field value.
	 */
	protected function get_ids_from_field( $value, array $field ) {
		if ( 'taxonomy' !== $field['type'] || ! pll_is_translated_taxonomy( $field['taxonomy'] ) ) {
			return array();
		}

		if ( is_numeric( $value ) ) {
			return array( (int) $value );
		}

		if ( is_array( $value ) ) {
			return array_map( 'intval', $value );
		}

		return array();
	}
}
