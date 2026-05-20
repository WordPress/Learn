<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

/**
 * This class is part of the ACF compatibility.
 * The collect post IDs strategy.
 * Gathers the IDs of the linked posts prior to export.
 *
 * @since 3.7
 */
class Collect_Post_Ids extends Abstract_Collect_Ids {

	/**
	 * Collects the post IDs of a field.
	 *
	 * @since 3.7
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @return int[] Custom field value.
	 */
	protected function get_ids_from_field( $value, array $field ) {
		switch ( $field['type'] ) {
			case 'image':
			case 'file':
				if ( PLL()->options['media_support'] && is_numeric( $value ) ) {
					return array( (int) $value );
				}
				break;
			case 'gallery':
				if ( PLL()->options['media_support'] && is_array( $value ) ) {
					return array_map( 'intval', $value );
				}
				break;
		}

		return array();
	}
}
