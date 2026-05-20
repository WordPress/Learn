<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class handling multiple or single translated objects export.
 *
 * @since 3.6
 */
abstract class PLL_Export_Translated_Objects {
	/**
	 * Translated object.
	 *
	 * @var PLL_Translated_Object
	 */
	protected $translated_object;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Translated_Object $translated_object Translated object.
	 */
	public function __construct( PLL_Translated_Object $translated_object ) {
		$this->translated_object = $translated_object;
	}

	/**
	 * Adds multiple items to export.
	 * Expects all items to belong to the same language, corresponding to export source.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $export_container Export container.
	 * @param object[]             $items            Items to export.
	 * @param PLL_Language         $target_language  Language to translate into.
	 * @return void
	 */
	public function add_items( PLL_Export_Container $export_container, array $items, PLL_Language $target_language ) {
		$tr_ids = array();

		foreach ( $items as $item ) {
			$tr_ids[] = $this->translated_object->get( $this->get_item_id( $item ), $target_language );
		}

		$tr_ids = array_filter( $tr_ids );

		if ( ! empty( $tr_ids ) ) {
			$this->add_to_cache( $tr_ids );
		}

		foreach ( $items as $item ) {
			$source_language = $this->translated_object->get_language( $this->get_item_id( $item ) );

			if ( empty( $source_language ) ) {
				continue;
			}

			$this->add_item( $export_container->get( $source_language, $target_language ), $item );
		}
	}

	/**
	 * Adds one item to export.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Data $export Export object.
	 * @param object          $item   Item to export.
	 * @return void
	 */
	abstract public function add_item( PLL_Export_Data $export, $item );

	/**
	 * Removes duplicate items from array.
	 *
	 * @since 3.6
	 *
	 * @param array $items An array of items of the same instance.
	 * @return array Array without duplicate items.
	 */
	public function remove_duplicate_items( array $items ): array {
		$all_items = array();

		foreach ( $items as $item ) {
			if ( ! isset( $all_items[ $this->get_item_id( $item ) ] ) ) {
				$all_items[ $this->get_item_id( $item ) ] = $item;
			}
		}

		return $all_items;
	}

	/**
	 * Caches objects to avoid too many SQL queries during export.
	 *
	 * @since 3.6
	 *
	 * @param int[] $ids Object IDs.
	 * @return void
	 *
	 * @phpstan-param non-empty-array<positive-int> $ids
	 */
	abstract protected function add_to_cache( array $ids );

	/**
	 * Returns ID corresponding to the given item.
	 *
	 * @since 3.6
	 *
	 * @param object $item Item to get ID from.
	 * @return int Object ID.
	 *
	 * @phpstan-return int<0, max>
	 */
	abstract protected function get_item_id( $item ): int;
}
