<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Import_Export\Services;

use Translation_Entry;

/**
 * Class to manage context data from `Translation_Entry` objects.
 *
 * @since 3.6
 */
class Context {
	const FIELD    = 'field';
	const ID       = 'id';
	const ENCODING = 'encoding';

	/**
	 * Returns stringified context suitable for `Translation_entry`.
	 *
	 * @since 3.6
	 *
	 * @param array $context Array of data to convert to string for `Translation_entry` context.
	 * @return string String usable with `Translation_entry`.
	 *
	 * @phpstan-param $context array<'encoding'|'field'|'id', string>
	 */
	public static function to_string( array $context ): string {
		$context = array_merge( static::get_default(), $context );
		$context = wp_json_encode( $context );

		return false === $context ? '' : $context;
	}

	/**
	 * Returns array of data from a `Translation_entry` context.
	 *
	 * @since 3.6
	 *
	 * @param string $context Raw context from a `Translation_Entry`.
	 * @return array Extracted array of data.
	 *
	 * @phpstan-return array<'encoding'|'field'|'id', string>
	 */
	public static function to_array( string $context ): array {
		$context = json_decode( $context, true );
		$context = is_array( $context ) ? $context : array();
		$context = array_filter( $context, 'is_string' );
		$default = static::get_default();

		return array_merge( $default, array_intersect_key( $context, $default ) );
	}

	/**
	 * Returns allowed context keys with empty values.
	 *
	 * @since 3.6
	 *
	 * @return array Context array keys with empty values.
	 *
	 * @phpstan-return array<'encoding'|'field'|'id', string>
	 */
	public static function get_default(): array {
		return array(
			self::FIELD    => '',
			self::ID       => '',
			self::ENCODING => '',
		);
	}

	/**
	 * Returns field context.
	 *
	 * @since 3.6
	 *
	 * @param Translation_Entry $entry Entry to get field from.
	 * @return string
	 */
	public static function get_field( Translation_Entry $entry ): string {
		$context = self::to_array( $entry->context );

		return $context[ static::FIELD ];
	}

	/**
	 * Returns id context.
	 *
	 * @since 3.6
	 *
	 * @param Translation_Entry $entry Entry to get id from.
	 * @return string
	 */
	public static function get_id( Translation_Entry $entry ): string {
		$context = self::to_array( $entry->context );

		return $context[ static::ID ];
	}

	/**
	 * Returns encoding context.
	 *
	 * @since 3.6
	 *
	 * @param Translation_Entry $entry Entry to get encoding from.
	 * @return string
	 */
	public static function get_encoding( Translation_Entry $entry ): string {
		$context = self::to_array( $entry->context );

		return $context[ static::ENCODING ];
	}
}
