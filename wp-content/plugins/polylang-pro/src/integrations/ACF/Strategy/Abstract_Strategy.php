<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;
use PLL_Language;

/**
 * This class is part of the ACF compatibility.
 * Abstract class for the translation strategies.
 * Holds logic common to all strategies, mostly related to field structure.
 *
 * @since 3.7
 */
abstract class Abstract_Strategy {
	/**
	 * @var bool[]
	 */
	private $can_execute = array();

	/**
	 * Checks if the translation strategy can be applied.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	public function can_execute( array $field ): bool {
		$key = $field['key'];

		if ( ! isset( $this->can_execute[ $key ] ) ) {
			$this->can_execute[ $key ] = $this->can_execute_recursive( $field );
		}

		return $this->can_execute[ $key ];
	}

	/**
	 * Applies the translation strategy.
	 *
	 * Depending on the type of fields, this will copy / synchronize a layout or
	 * auto-translate object ids.
	 *
	 * @since 3.7
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

		if ( ! acf_is_field_type( $field['type'] ) ) {
			// Avoid error if, for example, a repeater is created in ACF Pro and used later in ACF.
			return $value;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		switch ( $field['type'] ) {
			/*
			 * For ACF `flexible_content`, `repeater`, `clone` and `group` fields, ACF gets their real value (null) as their original value
			 * and not an array of their subfields. So, in this case, we force this original value to an empty array to be able to call
			 * `apply_on_rows()` method to copy subfields and make their translation.
			 */
			case 'flexible_content':
				if ( is_array( $value ) ) {
					$args['original_value'] = is_array( $args['original_value'] ) ? $args['original_value'] : array();
					$value = $this->apply_on_layouts( $object, $value, $field, $args );
				}
				break;
			case 'repeater':
				if ( is_array( $value ) ) {
					$args['original_value'] = is_array( $args['original_value'] ) ? $args['original_value'] : array();
					$value = $this->apply_on_rows( $object, $value, $field, $args );
				}
				break;
			case 'clone':
			case 'group':
				if ( is_array( $value ) ) {
					$args['original_value'] = is_array( $args['original_value'] ) ? $args['original_value'] : array();
					$value = $this->apply_on_group( $object, $value, $field, $args );
				}
				break;
			default:
				$value = $this->apply( $object, $value, $field, $args );
		}

		return $value;
	}

	/**
	 * Recursively checks if the translation strategy can be applied.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	protected function can_execute_recursive( array $field ): bool {
		if ( ! acf_is_field_type( $field['type'] ) ) {
			// Avoid error if, for example, a repeater is created in ACF Pro and used later in ACF.
			return false;
		}

		switch ( $field['type'] ) {
			case 'flexible_content':
				foreach ( $field['layouts'] as $layout ) {
					foreach ( $layout['sub_fields'] as $sub_field ) {
						if ( $this->can_execute( $sub_field ) ) {
							return true;
						}
					}
				}
				break;

			case 'clone':
			case 'group':
			case 'repeater':
				foreach ( $field['sub_fields'] as $sub_field ) {
					if ( $this->can_execute( $sub_field ) ) {
						return true;
					}
				}
				break;
		}

		return false;
	}

	/**
	 * Executes the strategy on a given field.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   Array of arguments.
	 * @return mixed Custom field value of the target object.
	 */
	abstract protected function apply( Abstract_Object $object, $value, array $field, array $args = array() );

	/**
	 * Copies or synchronizes subfields in a repeater or flexible content field.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param array           $values Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 *     Array of arguments.
	 *
	 *     @type mixed $original_value The translated value of the field, if any.
	 * }
	 * @return array Custom field value of the target object.
	 */
	protected function apply_on_rows( Abstract_Object $object, array $values, array $field, array $args = array() ): array {
		if ( empty( $field['sub_fields'] ) ) {
			return $values;
		}

		$original_value = $args['original_value'];

		foreach ( $field['sub_fields'] as $subfield ) {
			foreach ( $values as $row => $subvalues ) {
				if ( ! is_array( $subvalues ) ) {
					continue;
				}


				$subfield['pll_key']    = $this->get_field_key( $field ) . '_' . $row . '_' . $subfield['key'];  // Adds an entry in `subfield` with the full path of the field.
				$args['original_value'] = $original_value[ $row ] ?? null;
				$values[ $row ]         = $this->apply_on_subfield(
					$object,
					$subvalues,
					$subfield,
					$args
				);
			}
		}

		return $values;
	}

	/**
	 * Walks through layouts and apply the strategy to their subfields.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param array           $values Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   Array of arguments.
	 * @return array Custom field value of the target object.
	 */
	protected function apply_on_layouts( Abstract_Object $object, array $values, array $field, array $args = array() ): array {
		if ( empty( $field['layouts'] ) ) {
			return $values;
		}

		foreach ( $field['layouts'] as $layout ) {
			if ( empty( $layout['sub_fields'] ) ) {
				continue;
			}

			$values = $this->apply_on_rows( $object, $values, $layout, $args );
		}

		return $values;
	}

	/**
	 * Copies or synchronizes sub fields in a group or clone field.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param array           $values Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   Array of arguments.
	 * @return array Custom field value of the target object.
	 */
	protected function apply_on_group( Abstract_Object $object, array $values, array $field, array $args = array() ): array {
		foreach ( $field['sub_fields'] as $subfield ) {
			$subfield['pll_key'] = $this->get_field_key( $subfield ) . '_' . $subfield['key']; // Adds an entry in `subfield` with the full path of the field.
			$values              = $this->apply_on_subfield(
				$object,
				$values,
				$subfield,
				$args
			);
		}

		return $values;
	}

	/**
	 * Copies or synchronizes subfield values.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object    ACF object.
	 * @param array           $subvalues Custom field values of the source object.
	 * @param array           $subfield  Custom subfield definition.
	 * @param array           $args      {
	 *     Array of arguments.
	 *
	 *     @type mixed $original_value The translated value of the field, if any.
	 * }
	 * @return array Custom field value of the target object.
	 */
	protected function apply_on_subfield( Abstract_Object $object, array $subvalues, array $subfield, array $args = array() ): array {
		if ( empty( $subfield['parent'] ) ) {
			// Not a subfield.
			return $subvalues;
		}

		if ( ! $this->can_execute( $subfield ) ) {
			if ( isset( $args['original_value'][ $subfield['key'] ] ) ) {
				// If original values are hold with field key.
				$subvalues[ $subfield['key'] ] = $args['original_value'][ $subfield['key'] ];
			} elseif ( isset( $args['original_value'][ $subfield['name'] ] ) ) {
				// If original values are hold with field name.
				$subvalues[ $subfield['name'] ] = $args['original_value'][ $subfield['name'] ];
			} else {
				// Strip out the subfield if it can't be executed so the value won't be processed.
				unset( $subvalues[ $subfield['name'] ], $subvalues[ $subfield['key'] ] );
			}
			return $subvalues;
		}

		if ( isset( $subvalues[ $subfield['name'] ] ) ) {
			// Is the group value update with field name as key?
			$selector = $subfield['name'];
		} elseif ( isset( $subvalues[ $subfield['key'] ] ) ) {
			// Is the group value update with field key as key?
			$selector = $subfield['key'];
		}

		if ( empty( $selector ) ) {
			return $subvalues;
		}

		$args['original_value'] = $args['original_value'][ $selector ] ?? null;

		$subvalues[ $selector ] = $this->execute(
			$object,
			$subvalues[ $selector ],
			$subfield,
			$args
		);

		return $subvalues;
	}

	/**
	 * Returns the field key to use.
	 * Used to have a steady way of finding nested fields.
	 *
	 * @since 3.7
	 *
	 * @param array $field Field definition.
	 * @return string Field key.
	 */
	protected function get_field_key( array $field ): string {
		/*
		 * Why look for these keys in this order?
		 * #1: `pll_key` should be defined most of the time, it's used to prepend parent key
		 *     to keep track of fields hierarchy and identify them easily during export/import.
		 * #2: `_key` ensures to look for the original key in case the field is a seamless clone for instance.
		 * #3: `key` the standard field key.
		 */
		return $field['pll_key'] ?? $field['__key'] ?? $field['key'];
	}

	/**
	 * Determines if a field's value is the default value.
	 *
	 * @since 3.8
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @param array $args {
	 *      Array of arguments.
	 *
	 *      @type PLL_Language $source_language Optional. The language object of the source object.
	 *      @type mixed        $original_value  Optional. The translated value of the field, if any.
	 * }
	 *
	 * @return bool True if it's the default value, false otherwise.
	 */
	protected function is_field_default_value( $value, array $field, array $args = array() ): bool {
		if ( ! isset( $field['pll_default_value'], $args['source_language'] ) || ! $args['source_language'] instanceof PLL_Language ) {
			return false;
		}

		$default_value_in_source_language = $this->get_translated_default_value( $field, $args['source_language'] );

		return $default_value_in_source_language === $value;
	}

	/**
	 * Translates the field default value if applicable.
	 *
	 * @since 3.7.2
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @param array $args  Array of arguments.
	 * @return mixed Custom field value of the target object.
	 */
	protected function maybe_translate_field_default_value( $value, array $field, array $args = array() ) {
		return $this->is_field_default_value( $value, $field, $args ) ? $args['original_value'] : $value;
	}

	/**
	 * Gets the translated default value for a field in the specified language.
	 *
	 * @since 3.8
	 *
	 * @param array        $field         Custom field definition.
	 * @param PLL_Language $language      The specified language object.
	 * @return mixed The translated default value, or the original if no translation exists.
	 */
	protected function get_translated_default_value( array $field, PLL_Language $language ) {
		return ! is_string( $field['pll_default_value'] ) ? $field['pll_default_value'] : pll_translate_string( $field['pll_default_value'], $language->slug );
	}
}
