<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\CLI\Command;

/**
 * Adapter for setting transformations.
 *
 * Adapts between user-facing CLI interface and internal settings structure.
 * Allows to simplify the interface for the user changing key names or rendering prettier values.
 *
 * @since 3.8
 */
class Setting_Adapter {

	/**
	 * Returns the arguments adapted from user-facing interface to internal structure.
	 *
	 * @since 3.8
	 *
	 * @param array $args The arguments.
	 * @return array The adapted arguments.
	 */
	public function get_args( array $args ): array {
		if ( 'media_duplicate' === $args[0] ) {
			return array(
				'media',
				array(
					'duplicate' => $args[1],
				),
			);
		}

		return $args;
	}

	/**
	 * Returns the associative arguments adapted from user-facing interface to internal structure.
	 *
	 * @since 3.8
	 *
	 * @param array $assoc_args The associative arguments.
	 * @return array The adapted associative arguments.
	 */
	public function get_assoc_args( array $assoc_args ): array {
		if ( ! isset( $assoc_args['keys'] ) ) {
			return $assoc_args;
		}

		$assoc_args['keys'] = preg_replace( '/\bmedia_duplicate\b/', 'media', $assoc_args['keys'] );

		return $assoc_args;
	}

	/**
	 * Returns the setting item adapted from internal structure to user-facing interface.
	 *
	 * @since 3.8
	 *
	 * @param string $key      The key of the item.
	 * @param mixed  $value    The value of the item.
	 * @param array  $property The property of the item.
	 * @return array The adapted item.
	 */
	public function get_item( string $key, $value, array $property ): array {
		if ( 'media' !== $key ) {
			return array(
				'key'   => $key,
				'value' => $value,
				'type'  => $property['type'],
			);
		}

		/** @var array{duplicate: bool} $value */
		return array(
			'key'   => 'media_duplicate',
			'value' => $value['duplicate'],
			'type'  => 'boolean',
		);
	}
}
