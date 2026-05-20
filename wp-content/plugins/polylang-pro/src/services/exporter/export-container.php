<?php
/**
 * @package Polylang-Pro
 */

/**
 * Creates and stores instances of `PLL_Export_Data`.
 *
 * @since 3.6
 *
 * @implements IteratorAggregate<string, PLL_Export_Data>
 */
class PLL_Export_Container implements IteratorAggregate, Countable {

	/**
	 * Name of the class defining an individual export.
	 *
	 * @var string
	 *
	 * @phpstan-var class-string<PLL_Export_Data>
	 */
	private $class_name;

	/**
	 * Contains all the exports.
	 * Each export is referenced with a key composed of its source and target languages.
	 *
	 * @var PLL_Export_Data[]
	 *
	 * @phpstan-var array<string, PLL_Export_Data>
	 */
	private $exports = array();

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param string $class_name Name of the class that defines an individual export. The class must implement
	 *                           the interface `PLL_Export_Data`.
	 *
	 * @phpstan-param class-string<PLL_Export_Data> $class_name
	 */
	public function __construct( string $class_name ) {
		$this->class_name = $class_name;
	}

	/**
	 * Returns an export object for the given source/target languages pair.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Language $source_language The export's source language.
	 * @param PLL_Language $target_language The export's target language.
	 * @return PLL_Export_Data
	 */
	public function get( PLL_Language $source_language, PLL_Language $target_language ): PLL_Export_Data {
		$export_key = "{$source_language->slug}/{$target_language->slug}";
		$class_name = $this->class_name;

		if ( ! array_key_exists( $export_key, $this->exports ) ) {
			$this->exports[ $export_key ] = new $class_name( $source_language, $target_language );
		}

		return $this->exports[ $export_key ];
	}

	/**
	 * Returns an exports iterator.
	 * Needed for the interface `IteratorAggregate`.
	 *
	 * @since 3.6
	 *
	 * @return ArrayIterator
	 *
	 * @phpstan-return ArrayIterator<string, PLL_Export_Data>
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->exports );
	}

	/**
	 * Returns the number of exports.
	 * Needed for the interface `Countable`.
	 *
	 * @since 3.6
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->exports );
	}
}
