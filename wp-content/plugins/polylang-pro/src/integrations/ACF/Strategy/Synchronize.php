<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use PLL_Language;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;

/**
 * This class is part of the ACF compatibility.
 * Synchronization strategy.
 * Synchronizes the custom fields value from the source object to the target object.
 * Honors translations settings.
 *
 * @since 3.7
 */
class Synchronize extends Copy {
	/**
	 * @var Copy
	 */
	protected $copy;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param Copy $copy Copy Strategy.
	 */
	public function __construct( Copy $copy ) {
		$this->copy = $copy;
	}

	/**
	 * Applies the translation strategy.
	 *
	 * Depending on the type of fields, this will copy / synchronize a layout or
	 * auto-translate object ids.
	 *
	 * @since 3.7.1
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 *     Array of arguments.
	 *
	 *     @type mixed $original_value Optional. The translated value of the field, if any.
	 * }
	 * @return mixed Custom field value of the target object.
	 */
	public function execute( Abstract_Object $object, $value, array $field, array $args = array() ) {
		$args = wp_parse_args( $args, array( 'original_value' => null ) );

		if ( ! $this->can_execute( $field ) ) {
			return $args['original_value'];
		}

		if ( 'taxonomy' !== $field['type'] ) {
			return parent::execute( $object, $value, $field, $args );
		}

		if ( ! isset( $args['target_language'] ) || ! $args['target_language'] instanceof PLL_Language ) {
			return $value;
		}

		if ( ! pll_is_translated_taxonomy( $field['taxonomy'] ) ) {
			// Do not go any further if a taxonomy is not translatable.
			return $value;
		}

		$value = $this->translate_term( $value, $args['target_language'] );

		if ( ! empty( $field['save_terms'] ) && isset( $args['target_id'] ) ) {
			/*
			 * Save terms for the target object.
			 *
			 * The `acf_field_taxonomy::save_post()` method which assigns terms to the post fired
			 * by the `'acf/save_post'`action, is only fired for the source post.
			 * So we have to assign them to the target post ourselves.
			 */
			wp_set_object_terms( $args['target_id'], $value, $field['taxonomy'] );
		}

		return $value;
	}

	/**
	 * Recursively checks if a field can be synchronized.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	protected function can_execute_recursive( array $field ): bool {
		if ( isset( $field['translations'] ) && 'sync' === $field['translations'] ) {
			return true;
		}

		if ( ! acf_is_field_type( $field['type'] ) ) {
			// Avoid error if, for example, a repeater is created in ACF Pro and used later in ACF.
			return false;
		}

		switch ( $field['type'] ) {
			case 'clone':
			case 'group':
			case 'repeater':
				foreach ( $field['sub_fields'] as $sub_field ) {
					// A child field is synchronized or translatable. Let's synchronize the parent field.
					if ( isset( $sub_field['translations'] ) && 'translate' === $sub_field['translations'] ) {
						return true;
					}

					if ( $this->can_execute( $sub_field ) ) {
						return true;
					}
				}
				break;

			case 'flexible_content':
				foreach ( $field['layouts'] as $layout ) {
					foreach ( $layout['sub_fields'] as $sub_field ) {
						// A child field is synchronized or translatable. Let's synchronize the parent field.
						if ( isset( $sub_field['translations'] ) && 'translate' === $sub_field['translations'] ) {
							return true;
						}

						if ( $this->can_execute( $sub_field ) ) {
							return true;
						}
					}
				}
				break;
		}

		return false;
	}

	/**
	 * Copies subfields in a repeater or flexible content field.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param array           $values Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 * Array of arguments.
	 *
	 *      @type PLL_Language $target_language Language object of the target object.
	 *      @type array        $original_value  The value to return if the field must not be synced/copied. Basically it's
	 *                                          the field's original value.
	 * }
	 * @return array Custom field value of the target object.
	 */
	protected function apply_on_rows( Abstract_Object $object, array $values, array $field, array $args = array() ): array {
		if ( empty( $field['sub_fields'] ) ) {
			return $values;
		}

		foreach ( $field['sub_fields'] as $subfield ) {
			foreach ( $values as $row => $subvalues ) {
				if ( preg_match( '/^row-(?<incr>.+)$/', (string) $row, $matches ) ) {
					// Row already exists, let's update it.
					if ( ! is_array( $subvalues ) ) {
						continue;
					}

					$i                   = $matches['incr'];
					$parent              = $this->get_field_key( $field );
					$subfield['pll_key'] = $parent . '_' . $i . '_' . $subfield['key'];  // Adds an entry in `subfield` with the full path of the field.
					$values[ $row ]      = $this->apply_on_subfield(
						$object,
						$subvalues,
						$subfield,
						array(
							'target_language' => $args['target_language'],
							'original_value'  => $args['original_value'][ $i ] ?? null,
						)
					);

					continue;
				}

				// New row added, let's copy it.
				$parent              = $this->get_field_key( $field );
				$subfield['pll_key'] = $parent . '_' . $row . '_' . $subfield['key'];  // Adds an entry in `subfield` with the full path of the field.
				$values[ $row ]      = $this->copy->apply_on_subfield(
					$object,
					$subvalues,
					$subfield,
					array(
						'target_language' => $args['target_language'],
						'original_value'  => null,
					)
				);
			}
		}

		return $values;
	}
}
