<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use PLL_Export_Data;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;

/**
 * This class is part of the ACF compatibility.
 * Export strategy.
 * Adds custom fields value to data export object.
 *
 * @since 3.7
 */
class Export extends Abstract_Strategy {

	/**
	 * @var PLL_Export_Data The export object.
	 */
	protected $export;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Export_Data $export The export object.
	 * @return void
	 */
	public function __construct( PLL_Export_Data $export ) {
		$this->export = $export;
	}

	/**
	 * Executes the strategy on a given field.
	 * Depending on the type of fields, this will add the fields with the translate option to the fields to export.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 *     Array of arguments.
	 *
	 *     @type mixed $original_value The translated or default value of the field, if any.
	 * }
	 * @return mixed The original value, so the strategy behaves like others.
	 */
	protected function apply( Abstract_Object $object, $value, array $field, array $args = array() ) {
		if ( 'translate_once' === $field['translations']
			&& 0 < PLL()->model->{$object->get_type()}->get( $object->get_id(), $args['target_language'] ) ) {
			// A translation exists and we're on a `translate_once` field, so return.
			return $value;
		}

		if ( ! is_string( $value ) || empty( $value ) ) {
			return $value;
		}

		$original_value = is_string( $args['original_value'] ) ? $args['original_value'] : '';

		if ( $this->is_field_default_value( $value, $field, $args ) ) {
			$translated = $this->get_translated_default_value( $field, $args['target_language'] );
			if ( $translated !== $field['pll_default_value'] ) {
				// This string has been translated in our Strings Translations, so DO NOT send it to the export.
				return $translated;
			}
		}

		$this->export->add_translation_entry(
			array(
				'object_type' => $object->get_type(),
				'field_type'  => 'acf',
				'field_id'    => $this->get_field_key( $field ),
				'object_id'   => $object->get_id(),
			),
			$value,
			$original_value === $field['default_value'] ? '' : $original_value // Do not export translated default values.
		);

		return $value;
	}

	/**
	 * Recursively checks if a field can be copied.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	protected function can_execute_recursive( array $field ): bool {
		if ( isset( $field['translations'] ) && in_array( $field['translations'], array( 'translate', 'translate_once' ), true ) ) {
			return true;
		}

		return parent::can_execute_recursive( $field );
	}
}
