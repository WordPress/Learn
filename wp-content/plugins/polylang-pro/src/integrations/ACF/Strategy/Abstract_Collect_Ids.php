<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Translatable_Entity_Interface;

/**
 * This class is part of the ACF compatibility.
 * The collect IDs strategy.
 * Gathers the IDs of the linked entities prior to export.
 *
 * @since 3.7
 */
abstract class Abstract_Collect_Ids extends Abstract_Strategy {

	/**
	 * @var int[] Entities IDs linked to an object.
	 */
	protected $linked_ids = array();

	/**
	 * Executes the strategy on a given field.
	 * Depending on the type of fields, this will add the collected IDs to the relevant property.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   Optional arguments, none here.
	 * @return mixed Untouched custom field value.
	 */
	protected function apply( Abstract_Object $object, $value, array $field, array $args = array() ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$this->linked_ids = array_merge(
			$this->linked_ids,
			$this->get_ids_from_field( $value, $field )
		);

		return $value;
	}

	/**
	 * Recursively checks if a field can be collected.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return bool
	 */
	protected function can_execute_recursive( array $field ): bool {
		if ( isset( $field['translations'] ) && 'ignore' !== $field['translations'] ) {
			return true;
		}

		return parent::can_execute_recursive( $field );
	}

	/**
	 * Returns the collected entities IDs.
	 *
	 * @since 3.7
	 *
	 * @param Translatable_Entity_Interface $object Object holding the logic to apply the strategy.
	 * @return int[]
	 */
	public function get( Translatable_Entity_Interface $object ): array {
		$object->apply_to_all_fields( $this );

		return $this->linked_ids;
	}

	/**
	 * Collects the object IDs of a field.
	 *
	 * @since 3.7
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @return int[] Custom field value.
	 */
	abstract protected function get_ids_from_field( $value, array $field );
}
