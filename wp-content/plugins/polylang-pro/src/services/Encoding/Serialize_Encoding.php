<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Services\Encoding;

use WP_Error;

/**
 * Allows to unserialize and serialize data.
 *
 * @since 3.8
 */
class Serialize_Encoding implements Data_Encoder_Interface {
	/**
	 * Name of the format.
	 *
	 * @since 3.8
	 */
	public const NAME = 'serialize';

	/**
	 * Unserializes the given serialized data.
	 *
	 * @since 3.8
	 *
	 * @param string $data Data.
	 * @return mixed|WP_Error Decoded data. A `WP_Error` object upon decoding failure.
	 */
	public function decode( string $data ) {
		return maybe_unserialize( $data );
	}

	/**
	 * Serializes the given data.
	 *
	 * @since 3.8
	 *
	 * @param mixed $data Data.
	 * @return string|WP_Error Encoded data. A `WP_Error` object upon decoding failure.
	 */
	public function encode( $data ) {
		$encoded = maybe_serialize( $data );

		if ( ! is_scalar( $encoded ) ) {
			return new WP_Error(
				'pll-serialize-encoding-error',
				sprintf(
					/* translators: %s is a format. */
					__( 'Error while encoding to format "%s".', 'polylang-pro' ),
					self::NAME
				)
			);
		}

		return (string) $encoded;
	}
}
